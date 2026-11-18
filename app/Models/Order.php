<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_number',
        'user_id',
        'customer_name',
        'customer_phone',
        'customer_address',
        'district',
        'thana',
        'post_code',
        'house_road',
        'is_gift',
        'gift_recipient_name',
        'gift_recipient_phone',
        'gift_recipient_address',
        'gift_message',
        'book_id',
        'items',
        'quantity',
        'unit_price',
        'shipping_cost',
        'discount_amount',
        'gift_wrap_fee',
        'total_amount',
        'payment_method',
        'payment_status',
        'payment_id',
        'transaction_id',
        'payment_phone',
        'status',
        'courier_name',
        'tracking_code',
        'admin_notes',
        'points_earned',
        'points_used',
        'affiliate_id',
        'commission_amount',
    ];

    protected $casts = [
        'items' => 'array',
        'is_gift' => 'boolean',
        'quantity' => 'integer',
        'unit_price' => 'float',
        'shipping_cost' => 'float',
        'discount_amount' => 'float',
        'gift_wrap_fee' => 'float',
        'total_amount' => 'float',
        'points_earned' => 'integer',
        'points_used' => 'integer',
        'commission_amount' => 'float',
    ];

    protected static function booted()
    {
        static::creating(function ($order) {
            if (empty($order->order_number)) {
                $order->order_number = static::generateOrderNumber();
            }
            if (empty($order->quantity)) {
                $order->quantity = 1;
            }
        });

        // A new order must show up for the admins straight away: refresh the bell count now and
        // email the shop once the customer's page has been sent (so checkout is not slowed down).
        static::created(function (Order $order) {
            try {
                \Illuminate\Support\Facades\Cache::forget('admin_dash_pending_alerts');
            } catch (\Throwable) {
            }
            if (!app()->runningUnitTests()) {
                $id = $order->id;
                dispatch(function () use ($id) {
                    \App\Services\OrderAlertService::notifyAdmins($id);
                })->afterResponse();
            }
        });

        static::saved(function (Order $order) {
            if ($order->payment_status === 'paid' || ((float) $order->total_amount <= 0 && $order->status === 'confirmed')) {
                \App\Services\RoyaltyService::processOrderRoyalties($order);
            }
        });
    }

    public static function generateOrderNumber(): string
    {
        $year = date('Y');
        $prefix = "IDP-{$year}-";
        $len = strlen($prefix) + 1;

        $maxSeq = (int) static::where('order_number', 'like', "{$prefix}%")
            ->selectRaw("MAX(CAST(SUBSTRING(order_number, {$len}) AS UNSIGNED)) as max_seq")
            ->value('max_seq');

        $nextNumber = $maxSeq >= 1001 ? ($maxSeq + 1) : 1001;

        return $prefix . str_pad((string) $nextNumber, 4, '0', STR_PAD_LEFT);
    }

    /**
     * What was ordered, one row per item: [title, format, qty, price, type, id].
     * Orders placed before the `items` column existed are read back from the "অর্ডার আইটেম: …" note.
     *
     * @return array<int, array{title:string, format:?string, qty:int, price:?float, type:?string, id:?int}>
     */
    public function lineItems(): array
    {
        if (is_array($this->items) && $this->items !== []) {
            return array_map(fn ($i) => [
                'title'  => (string) ($i['title'] ?? 'পণ্য'),
                'format' => $i['format'] ?? null,
                'qty'    => (int) ($i['qty'] ?? 1),
                'price'  => isset($i['price']) ? (float) $i['price'] : null,
                'type'   => $i['type'] ?? null,
                'id'     => isset($i['id']) ? (int) $i['id'] : null,
            ], $this->items);
        }

        if (preg_match('/অর্ডার আইটেম:\s*([^|]+)/u', (string) $this->admin_notes, $m)) {
            $out = [];
            // Each item ends with "(xN)"; titles themselves may contain commas.
            preg_match_all('/\s*(.+?)\s*\(x(\d+)\)\s*(?:,|$)/u', trim($m[1]), $parts, PREG_SET_ORDER);
            foreach ($parts as $part) {
                $title = trim(preg_replace('/\s*\[EBOOK_ID:\d+\]/u', '', $part[1]));
                if ($title === '') {
                    continue;
                }
                $out[] = ['title' => $title, 'format' => str_contains($title, 'হার্ডকভার') ? 'hardcover' : null, 'qty' => (int) $part[2], 'price' => null, 'type' => str_contains($title, 'ই-বুক') ? 'ebook' : null, 'id' => null];
            }
            if ($out) {
                return $out;
            }
        }

        return $this->book
            ? [['title' => $this->book->title, 'format' => null, 'qty' => (int) ($this->quantity ?: 1), 'price' => (float) $this->unit_price, 'type' => 'book', 'id' => $this->book_id]]
            : [];
    }

    public function book()
    {
        return $this->belongsTo(\Modules\Book\Models\Book::class, 'book_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function affiliate()
    {
        return $this->belongsTo(User::class, 'affiliate_id');
    }

    public function getInvoiceNoAttribute(): string
    {
        return $this->order_number ?? ('IDP-' . date('Y') . '-' . str_pad((string)$this->id, 4, '0', STR_PAD_LEFT));
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'pending' => 'অপেক্ষমান (Pending)',
            'processing' => 'প্রক্রিয়াধীন (Processing)',
            'confirmed' => 'নিশ্চিত (Confirmed)',
            'shipped' => 'শিপিংয়ে পাঠানো (Shipped)',
            'delivered' => 'ডেলিভারি সম্পন্ন (Delivered)',
            'cancelled' => 'বাতিল (Cancelled)',
            'returned' => 'ফেরত (Returned)',
            default => ucfirst($this->status),
        };
    }

    public function getStatusBadgeAttribute(): string
    {
        return match ($this->status) {
            'pending' => 'warning',
            'processing' => 'info',
            'confirmed' => 'primary',
            'shipped' => 'indigo',
            'delivered' => 'success',
            'cancelled' => 'danger',
            'returned' => 'secondary',
            default => 'light',
        };
    }

    public function getPaymentMethodLabelAttribute(): string
    {
        return match ($this->payment_method) {
            'cod' => 'ক্যাশ অন ডেলিভারি (COD)',
            'bkash' => 'বিকাশ (bKash)',
            'nagad' => 'নগদ (Nagad)',
            'rocket' => 'রকেট (Rocket)',
            'bank' => 'ব্যাংক ট্রান্সফার (Bank)',
            'card', 'sslcommerz' => 'কার্ড / অনলাইন',
            default => strtoupper($this->payment_method ?? 'COD'),
        };
    }

    public function getPaymentStatusLabelAttribute(): string
    {
        return match ($this->payment_status) {
            'paid' => 'পরিশোধিত',
            'partial' => 'আংশিক পরিশোধ',
            'unpaid', 'pending' => 'বকেয়া / প্রদেয়',
            default => ucfirst($this->payment_status),
        };
    }

    public function getDistrictLabelAttribute(): string
    {
        return match ($this->district) {
            'dhaka' => 'ঢাকা সিটি',
            'dhaka_sub' => 'ঢাকা উপশহর / সাভার / গাজীপুর',
            'outside' => 'ঢাকার বাইরে সমগ্র বাংলাদেশ',
            default => $this->district ?? 'নির্দিষ্ট নয়',
        };
    }

    public function getFullAddressAttribute(): string
    {
        $parts = array_filter([
            $this->house_road,
            $this->customer_address,
            $this->thana ? "থানা/উপজেলা: {$this->thana}" : null,
            $this->post_code ? "পোস্ট: {$this->post_code}" : null,
            $this->district_label,
        ]);

        return implode(', ', $parts);
    }
}
