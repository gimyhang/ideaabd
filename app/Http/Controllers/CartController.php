<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\AdminDashboardSetting;
use App\Models\Order;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Modules\Book\Models\Book;

class CartController extends Controller
{
    /**
     * Display live shopping cart and instant checkout page.
     */
    public function index(): View
    {
        $settings = [];
        if (Schema::hasTable('admin_dashboard_settings')) {
            $settings = AdminDashboardSetting::all()->pluck('value', 'key')->toArray();
        }

        $ecomSetting = $settings['ecommerce_settings'] ?? [
            'delivery_dhaka'          => 50,
            'delivery_sub'            => 100,
            'delivery_outside'        => 120,
            'gift_wrap_fee'           => 20,
            'free_delivery_threshold' => 1500,
            'bkash_number'            => '01558712810',
            'nagad_number'            => '01558712810',
            'rocket_number'           => '01558712810',
        ];

        $paymentGateways = $settings['payment_gateways'] ?? [
            'cod' => [
                'enabled'      => true,
                'name'         => 'ক্যাশ অন ডেলিভারি (COD)',
                'instructions' => 'বই হাতে পেয়ে মূল্য পরিশোধ করুন।',
            ],
            'bkash' => [
                'enabled'      => true,
                'name'         => 'বিকাশ (bKash)',
                'number'       => $ecomSetting['bkash_number'] ?? '01558712810',
                'type'         => 'personal',
                'instructions' => 'বিকাশ সেন্ড মানি করে TrxID ও নম্বর দিন।',
            ],
            'nagad' => [
                'enabled'      => true,
                'name'         => 'নগদ (Nagad)',
                'number'       => $ecomSetting['nagad_number'] ?? '01558712810',
                'type'         => 'personal',
                'instructions' => 'নগদ সেন্ড মানি করে TrxID ও নম্বর দিন।',
            ],
            'rocket' => [
                'enabled'      => false,
                'name'         => 'রকেট (Rocket)',
                'number'       => $ecomSetting['rocket_number'] ?? '01558712810',
                'type'         => 'personal',
                'instructions' => 'রকেট একাউন্ট থেকে সেন্ড মানি করুন।',
            ],
        ];

        return view('frontend.pages.cart', compact('ecomSetting', 'paymentGateways'));
    }

    /**
     * Process checkout from cart drawer or cart page.
     */
    public function checkout(Request $request): RedirectResponse|JsonResponse
    {
        $validated = $request->validate([
            'customer_name'          => 'required|string|max:255',
            'customer_phone'         => 'required|string|max:25',
            'customer_address'       => 'required|string|max:1000',
            'district'               => 'required|string',
            'thana'                  => 'nullable|string|max:100',
            'post_code'              => 'nullable|string|max:20',
            'payment_method'         => 'nullable|string|in:cod,bkash,nagad,rocket,card,bank',
            'transaction_id'         => 'nullable|string|max:100',
            'payment_phone'          => 'nullable|string|max:30',
            'is_gift'                => 'nullable|boolean',
            'gift_recipient_name'    => 'nullable|required_if:is_gift,1|string|max:255',
            'gift_recipient_phone'   => 'nullable|required_if:is_gift,1|string|max:20',
            'gift_recipient_address' => 'nullable|required_if:is_gift,1|string',
            'gift_message'           => 'nullable|string',
            'cart_items'             => 'required|string', // JSON array of items: [{id, title, price, qty/quantity}]
        ]);

        $items = json_decode($validated['cart_items'], true);
        if (!is_array($items) || empty($items)) {
            if ($request->expectsJson()) {
                return response()->json(['success' => false, 'message' => 'আপনার কার্টে কোনো পণ্য নেই।'], 422);
            }
            return back()->with('error', 'আপনার কার্টে কোনো বই বা পণ্য পাওয়া যায়নি।');
        }

        // Fetch shipping settings from AdminDashboardSetting
        $ecomSettings = [];
        if (Schema::hasTable('admin_dashboard_settings')) {
            $settingRow = AdminDashboardSetting::where('key', 'ecommerce_settings')->first();
            $ecomSettings = $settingRow?->value ?? [];
        }

        $feeDhaka = floatval($ecomSettings['delivery_dhaka'] ?? 50);
        $feeSub = floatval($ecomSettings['delivery_sub'] ?? 100);
        $feeOutside = floatval($ecomSettings['delivery_outside'] ?? 120);
        $giftWrapFee = floatval($ecomSettings['gift_wrap_fee'] ?? 20);
        $freeThreshold = floatval($ecomSettings['free_delivery_threshold'] ?? 1500);

        // Calculate delivery charge
        $shippingCost = match ($validated['district']) {
            'dhaka' => $feeDhaka,
            'dhaka_sub' => $feeSub,
            default => $feeOutside,
        };

        // Calculate subtotal from items
        $subtotal = 0;
        $totalQuantity = 0;
        $primaryBookId = null;
        $titles = [];

        foreach ($items as $item) {
            $bookId = intval($item['id'] ?? 0);
            $qty = max(1, intval($item['qty'] ?? $item['quantity'] ?? 1));
            $price = floatval($item['price'] ?? 0);

            // If possible, verify against database book price
            if ($bookId > 0) {
                $dbBook = Book::find($bookId);
                if ($dbBook) {
                    $price = floatval($dbBook->discount_price ?? $dbBook->price);
                    if (!$primaryBookId) {
                        $primaryBookId = $dbBook->id;
                    }
                    $titles[] = $dbBook->title . " (x{$qty})";
                } else {
                    $titles[] = ($item['title'] ?? 'বই') . " (x{$qty})";
                }
            }

            $subtotal += ($price * $qty);
            $totalQuantity += $qty;
        }

        if ($freeThreshold > 0 && $subtotal >= $freeThreshold) {
            $shippingCost = 0;
        }

        $isGift = !empty($validated['is_gift']);
        $giftFee = $isGift ? $giftWrapFee : 0;
        $totalAmount = $subtotal + $shippingCost + $giftFee;

        // Fallback primary book ID if none matched
        if (!$primaryBookId) {
            $firstBook = Book::first();
            $primaryBookId = $firstBook?->id;
        }

        $paymentMethod = $validated['payment_method'] ?? 'cod';
        $paymentStatus = (!empty($validated['transaction_id']) && in_array($paymentMethod, ['bkash', 'nagad', 'rocket'])) ? 'paid' : 'pending';

        // Affiliate commission
        $affiliateId = null;
        $commissionAmount = 0;
        if (request()->cookie('ref_id')) {
            $affiliate = User::find(request()->cookie('ref_id'));
            if ($affiliate && $affiliate->id !== auth()->id()) {
                $affiliateId = $affiliate->id;
                $commissionAmount = $subtotal * 0.05;
                $affiliate->increment('affiliate_balance', $commissionAmount);
            }
        }

        // Loyalty points
        $pointsEarned = (int) (floor($totalAmount / 100) * 5);

        $order = Order::create([
            'user_id'                => auth()->id(),
            'customer_name'          => $validated['customer_name'],
            'customer_phone'         => $validated['customer_phone'],
            'customer_address'       => $validated['customer_address'],
            'district'               => $validated['district'],
            'thana'                  => $validated['thana'] ?? null,
            'post_code'              => $validated['post_code'] ?? null,
            'is_gift'                => $isGift,
            'gift_recipient_name'    => $validated['gift_recipient_name'] ?? null,
            'gift_recipient_phone'   => $validated['gift_recipient_phone'] ?? null,
            'gift_recipient_address' => $validated['gift_recipient_address'] ?? null,
            'gift_message'           => $validated['gift_message'] ?? null,
            'book_id'                => $primaryBookId,
            'quantity'               => $totalQuantity,
            'unit_price'             => $totalQuantity > 0 ? ($subtotal / $totalQuantity) : $subtotal,
            'shipping_cost'          => $shippingCost,
            'discount_amount'        => 0,
            'gift_wrap_fee'          => $giftFee,
            'total_amount'           => $totalAmount,
            'payment_method'         => $paymentMethod,
            'payment_status'         => $paymentStatus,
            'transaction_id'         => $validated['transaction_id'] ?? null,
            'payment_phone'          => $validated['payment_phone'] ?? null,
            'status'                 => 'pending',
            'admin_notes'            => 'অর্ডার আইটেম: ' . implode(', ', $titles),
            'points_earned'          => $pointsEarned,
            'affiliate_id'           => $affiliateId,
            'commission_amount'      => $commissionAmount,
        ]);

        if (auth()->check()) {
            auth()->user()->increment('loyalty_points', $pointsEarned);
        }

        $successMsg = "আপনার অর্ডারটি সফলভাবে সম্পন্ন হয়েছে! অর্ডার নম্বর: #{$order->order_number}। আমাদের প্রতিনিধি শীঘ্রই আপনার সাথে যোগাযোগ করবেন।";

        if ($request->expectsJson()) {
            return response()->json([
                'success'      => true,
                'order_number' => $order->order_number,
                'message'      => $successMsg,
                'redirect'     => route('home'),
            ]);
        }

        return redirect()->route('home')->with('success', $successMsg);
    }
}
