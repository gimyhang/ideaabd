<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Order;
use Modules\Book\Models\Book;
use App\Models\AdminDashboardSetting;
use Illuminate\Support\Facades\Schema;

class OrderController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'book_id' => 'required|exists:books,id',
            'customer_name' => 'required|string|max:255',
            'customer_phone' => 'required|string|max:20',
            'district' => 'required|string',
            'thana' => 'nullable|string|max:100',
            'post_code' => 'nullable|string|max:20',
            'customer_address' => 'required|string',
            'quantity' => 'nullable|integer|min:1|max:50',
            'payment_method' => 'nullable|string|in:cod,bkash,nagad,rocket,card',
            'transaction_id' => 'nullable|string|max:100',
            'payment_phone' => 'nullable|string|max:30',
            'is_gift' => 'nullable|boolean',
            'gift_recipient_name' => 'nullable|required_if:is_gift,1|string|max:255',
            'gift_recipient_phone' => 'nullable|required_if:is_gift,1|string|max:20',
            'gift_recipient_address' => 'nullable|required_if:is_gift,1|string',
            'gift_message' => 'nullable|string',
        ]);

        $book = Book::findOrFail($validated['book_id']);
        $quantity = intval($validated['quantity'] ?? 1);
        $unitPrice = floatval($book->discount_price ?? $book->price);
        $subtotal = $unitPrice * $quantity;

        // Fetch shipping settings from AdminDashboardSetting if available
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

        if ($freeThreshold > 0 && $subtotal >= $freeThreshold) {
            $shippingCost = 0;
        }

        $isGift = !empty($validated['is_gift']);
        $giftFee = $isGift ? $giftWrapFee : 0;
        $totalAmount = $subtotal + $shippingCost + $giftFee;

        $validated['quantity'] = $quantity;
        $validated['unit_price'] = $unitPrice;
        $validated['shipping_cost'] = $shippingCost;
        $validated['gift_wrap_fee'] = $giftFee;
        $validated['discount_amount'] = max(0, floatval(($book->price - $unitPrice) * $quantity));
        $validated['total_amount'] = $totalAmount;
        $validated['payment_method'] = $validated['payment_method'] ?? 'cod';
        $validated['payment_status'] = (!empty($validated['transaction_id']) && in_array($validated['payment_method'], ['bkash', 'nagad', 'rocket'])) ? 'paid' : 'pending';
        $validated['status'] = 'pending';
        $validated['user_id'] = auth()->id();

        // Loyalty Points Calculation (5 points per 100 Taka)
        $pointsEarned = floor($totalAmount / 100) * 5;
        $validated['points_earned'] = $pointsEarned;

        // Affiliate System
        if (request()->cookie('ref_id')) {
            $affiliate = \App\Models\User::find(request()->cookie('ref_id'));
            if ($affiliate && $affiliate->id !== auth()->id()) {
                $validated['affiliate_id'] = $affiliate->id;
                $commission = $subtotal * 0.05; // 5% commission
                $validated['commission_amount'] = $commission;
                $affiliate->increment('affiliate_balance', $commission);
            }
        }

        $order = Order::create($validated);

        if (auth()->check()) {
            auth()->user()->increment('loyalty_points', $pointsEarned);
        }

        return back()->with('success', "আপনার অর্ডারটি সফলভাবে গ্রহণ করা হয়েছে! অর্ডার নম্বর: #{$order->order_number}");
    }
}
