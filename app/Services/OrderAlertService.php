<?php

namespace App\Services;

use App\Models\Order;
use App\Support\SiteSetting;
use Illuminate\Support\Facades\Log;

/**
 * Everything that tells someone about a web order:
 *  - admins, when a new order comes in (email; optional SMS to a shop phone)
 *  - the admin panel's live alert (pulse(): newest order + pending count)
 *  - the customer, when the order moves to the next step (SMS)
 */
class OrderAlertService
{
    /** Customer-facing wording per status. Statuses not listed send nothing. */
    public const CUSTOMER_STATUS_TEXT = [
        'confirmed'  => 'নিশ্চিত করা হয়েছে',
        'processing' => 'প্যাকিং চলছে',
        'shipped'    => 'কুরিয়ারে পাঠানো হয়েছে',
        'delivered'  => 'ডেলিভারি সম্পন্ন হয়েছে',
        'cancelled'  => 'বাতিল করা হয়েছে',
        'returned'   => 'ফেরত এসেছে',
    ];

    /** The order's items as one short line: "বই A ×2, বই B (হার্ডকভার) ×1". */
    public static function itemsLine(Order $order, int $max = 4): string
    {
        $items = $order->lineItems();
        $parts = array_map(fn ($i) => $i['title'] . ($i['format'] === 'hardcover' && !str_contains($i['title'], 'হার্ডকভার') ? ' (হার্ডকভার)' : '') . ' ×' . $i['qty'], array_slice($items, 0, $max));
        if (count($items) > $max) {
            $parts[] = '+' . (count($items) - $max) . ' আরও';
        }

        return implode(', ', $parts);
    }

    /**
     * Tell the shop about a new order. Runs after the customer's response has been sent.
     * Email goes to the helpline email (Ecommerce settings) plus `order_alert_emails`;
     * an SMS is sent only when `order_alert_phone` is set, because SMS costs money.
     */
    public static function notifyAdmins(int $orderId): void
    {
        $order = Order::with('book')->find($orderId);
        if (!$order) {
            return;
        }

        $ecom = SiteSetting::ecommerce();
        $url = route('admin.ecommerce-orders', ['search' => $order->order_number]);
        $total = number_format((float) $order->total_amount, 2);
        $items = self::itemsLine($order);

        $emails = array_filter(array_unique(array_map('trim', array_merge(
            [SiteSetting::helplineEmail()],
            preg_split('/[\s,;]+/', (string) ($ecom['order_alert_emails'] ?? ''), -1, PREG_SPLIT_NO_EMPTY) ?: []
        ))), fn ($e) => filter_var($e, FILTER_VALIDATE_EMAIL));

        $subject = "নতুন অর্ডার #{$order->order_number} — ৳{$total}";
        $body = "নতুন একটি অর্ডার এসেছে।\n\n"
            . "অর্ডার: #{$order->order_number}\n"
            . "গ্রাহক: {$order->customer_name} ({$order->customer_phone})\n"
            . "ঠিকানা: {$order->full_address}\n"
            . "পণ্য: {$items}\n"
            . "মোট: ৳{$total} · পেমেন্ট: {$order->payment_method_label}"
            . ($order->transaction_id ? " · TrxID: {$order->transaction_id}" : '')
            . "\n\nঅর্ডারটি নিশ্চিত করতে নিচের বাটনে ক্লিক করুন।";

        foreach ($emails as $email) {
            try {
                EmailService::sendSingle($email, $subject, $body, 'অর্ডার দেখুন', $url, 'Admin');
            } catch (\Throwable $e) {
                Log::warning('New-order admin email failed: ' . $e->getMessage(), ['order' => $order->id, 'to' => $email]);
            }
        }

        $phone = trim((string) ($ecom['order_alert_phone'] ?? ''));
        if ($phone !== '') {
            try {
                SmsService::send($phone, "New order #{$order->order_number} Tk{$total} - {$order->customer_name} {$order->customer_phone}", [
                    'purpose' => 'order', 'once_key' => 'admin-new-order:' . $order->order_number,
                ]);
            } catch (\Throwable $e) {
                Log::warning('New-order admin SMS failed: ' . $e->getMessage(), ['order' => $order->id]);
            }
        }
    }

    /**
     * Live state for the admin panel: pending count and orders newer than $sinceId.
     * $sinceId null = this browser has not seen any order yet (first visit): nothing is announced.
     * 0 is a real value (the shop had no orders), so the very first order is still announced.
     */
    public static function pulse(?int $sinceId = null): array
    {
        $pending = Order::where('status', 'pending')->count();
        $latestId = (int) Order::max('id');
        $new = $sinceId !== null
            ? Order::with('book')->where('id', '>', $sinceId)->orderByDesc('id')->limit(5)->get()
            : collect();

        return [
            'pending'   => $pending,
            'latest_id' => $latestId,
            'new'       => $new->map(fn (Order $o) => [
                'id'     => $o->id,
                'number' => $o->order_number,
                'name'   => $o->customer_name,
                'phone'  => $o->customer_phone,
                'total'  => (float) $o->total_amount,
                'items'  => self::itemsLine($o, 2),
                'when'   => $o->created_at?->diffForHumans(),
                'url'    => route('admin.ecommerce-orders', ['search' => $o->order_number]),
            ])->values()->all(),
        ];
    }

    /**
     * SMS the customer that their order moved on. Each status is sent at most once per order (once_key).
     * Returns true when an SMS was attempted.
     */
    public static function notifyCustomer(Order $order): bool
    {
        $text = self::CUSTOMER_STATUS_TEXT[$order->status] ?? null;
        if (!$text || empty($order->customer_phone)) {
            return false;
        }

        $site = 'আইডিয়া প্রকাশন';
        $msg = "{$site}: আপনার অর্ডার #{$order->order_number} {$text}।";
        if ($order->status === 'shipped' && ($order->courier_name || $order->tracking_code)) {
            $msg .= ' কুরিয়ার: ' . trim($order->courier_name . ' ' . ($order->tracking_code ? "ট্র্যাকিং {$order->tracking_code}" : ''));
        }
        if ($order->status === 'confirmed') {
            $msg .= ' মোট ৳' . number_format((float) $order->total_amount) . '।';
        }
        $msg .= ' ' . url('/track-order?order_no=' . urlencode((string) $order->order_number));

        try {
            SmsService::send($order->customer_phone, $msg, [
                'purpose' => 'order', 'once_key' => 'order-status:' . $order->order_number . ':' . $order->status,
            ]);

            return true;
        } catch (\Throwable $e) {
            Log::warning('Order status SMS failed: ' . $e->getMessage(), ['order' => $order->id]);

            return false;
        }
    }
}
