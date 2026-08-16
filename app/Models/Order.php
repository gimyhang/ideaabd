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
        'quantity',
        'unit_price',
        'shipping_cost',
        'discount_amount',
        'gift_wrap_fee',
        'total_amount',
        'payment_method',
        'payment_status',
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
    }

    public static function generateOrderNumber(): string
    {
        $year = date('Y');
        $prefix = "IDP-{$year}-";
        $latest = static::where('order_number', 'like', "{$prefix}%")->latest('id')->first();
        
        if ($latest && preg_match('/-(\d+)$/', $latest->order_number, $matches)) {
            $nextNumber = intval($matches[1]) + 1;
        } else {
            $nextNumber = 1001;
        }
        
        return $prefix . str_pad((string)$nextNumber, 4, '0', STR_PAD_LEFT);
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
            'card' => 'কার্ড / অনলাইন',
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
