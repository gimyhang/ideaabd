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

        $rawEcom = $settings['ecommerce_settings'] ?? [];
        $ecomSetting = array_merge([
            'delivery_dhaka'          => 50,
            'delivery_sub'            => 100,
            'delivery_outside'        => 120,
            'gift_wrap_fee'           => 20,
            'free_delivery_threshold' => 1500,
            'bkash_number'            => '01558712810',
            'nagad_number'            => '01558712810',
            'rocket_number'           => '01558712810',
            'payment_instruction'     => 'বিকাশ বা নগদ থেকে উল্লেখিত নম্বরে সেন্ড মানি করে TrxID ও পেমেন্ট নম্বর দিন।',
            // Coupon Configuration
            'coupon_enabled'          => false,
            'coupon_code'             => 'IDEA2026',
            'coupon_type'             => 'percent', // 'percent' or 'fixed'
            'coupon_discount'         => 10,
            'coupon_min_order'        => 500,
            'coupon_description'      => 'বিশেষ কুপন ছাড়',
            // Threshold Offer Configuration
            'threshold_offer_enabled' => false,
            'threshold_offer_amount'  => 1000,
            'threshold_offer_type'    => 'free_delivery', // 'free_delivery', 'flat_discount', 'percent_discount'
            'threshold_offer_discount'=> 100,
            'threshold_offer_title'   => '৳১০০০+ অর্ডারে ফ্রি ডেলিভারি ও বিশেষ উপহার!',
        ], is_array($rawEcom) ? $rawEcom : []);

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
     * Validate Coupon Code via AJAX.
     */
    public function validateCoupon(Request $request): JsonResponse
    {
        $request->validate([
            'coupon_code' => 'required|string|max:50',
            'subtotal'    => 'required|numeric|min:0',
        ]);

        $subtotal = floatval($request->input('subtotal', 0));
        $inputCode = strtoupper(trim($request->input('coupon_code')));

        $ecomSettings = [];
        if (Schema::hasTable('admin_dashboard_settings')) {
            $settingRow = AdminDashboardSetting::where('key', 'ecommerce_settings')->first();
            $ecomSettings = $settingRow?->value ?? [];
        }

        $couponEnabled = !empty($ecomSettings['coupon_enabled']);
        $configCode = strtoupper(trim($ecomSettings['coupon_code'] ?? 'IDEA2026'));
        $couponType = $ecomSettings['coupon_type'] ?? 'percent';
        $couponDiscount = floatval($ecomSettings['coupon_discount'] ?? 10);
        $couponMinOrder = floatval($ecomSettings['coupon_min_order'] ?? 500);
        $couponDesc = $ecomSettings['coupon_description'] ?? 'কুপন ছাড়';

        if (!$couponEnabled) {
            return response()->json([
                'valid'   => false,
                'message' => 'দুঃখিত, বর্তমানে কোনো কুপন অফার সক্রিয় নেই।',
            ], 422);
        }

        if ($inputCode !== $configCode) {
            return response()->json([
                'valid'   => false,
                'message' => 'প্রদত্ত কুপন কোডটি সঠিক নয়। অনুগ্রহ করে পুনরায় চেক করুন।',
            ], 422);
        }

        if ($subtotal < $couponMinOrder) {
            return response()->json([
                'valid'   => false,
                'message' => "এই কুপনটি ব্যবহারের জন্য সর্বনিম্ন ৳" . number_format($couponMinOrder) . " টাকার বই অর্ডার করতে হবে।",
            ], 422);
        }

        $discountAmount = ($couponType === 'percent')
            ? round(($subtotal * $couponDiscount) / 100)
            : min($subtotal, $couponDiscount);

        return response()->json([
            'valid'           => true,
            'code'            => $configCode,
            'discount_amount' => $discountAmount,
            'discount_type'   => $couponType,
            'discount_rate'   => $couponDiscount,
            'description'     => $couponDesc,
            'message'         => "অভিনন্দন! কুপন প্রয়োগ হয়েছে। আপনি ৳" . number_format($discountAmount) . " ছাড় পেয়েছেন।",
        ]);
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
            'coupon_code'            => 'nullable|string|max:50',
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

        // Fetch shipping & offer settings from AdminDashboardSetting
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
            
            // Clean price from any string or formatted value
            $priceRaw = $item['price'] ?? 0;
            $price = is_numeric($priceRaw) ? floatval($priceRaw) : floatval(preg_replace('/[^\d.]/', '', (string)$priceRaw));

            // If possible, verify against database book price
            if ($bookId > 0) {
                $dbBook = Book::find($bookId);
                if ($dbBook) {
                    $itemFormat = strtolower($item['format'] ?? '');
                    if ($itemFormat === 'hardcover' && !empty($dbBook->hardcover_price)) {
                        $price = floatval($dbBook->hardcover_discount_price > 0 && $dbBook->hardcover_discount_price < $dbBook->hardcover_price ? $dbBook->hardcover_discount_price : $dbBook->hardcover_price);
                    } else {
                        $price = floatval($dbBook->discount_price > 0 && $dbBook->discount_price < $dbBook->price ? $dbBook->discount_price : $dbBook->price);
                    }
                    if (!$primaryBookId) {
                        $primaryBookId = $dbBook->id;
                    }
                    $formatSuffix = $itemFormat === 'hardcover' ? ' [হার্ডকভার]' : '';
                    $titles[] = $dbBook->title . $formatSuffix . " (x{$qty})";
                } else {
                    $titles[] = ($item['title'] ?? 'বই') . " (x{$qty})";
                }
            }

            $subtotal += ($price * $qty);
            $totalQuantity += $qty;
        }

        // Automatic Free Shipping Threshold
        if ($freeThreshold > 0 && $subtotal >= $freeThreshold) {
            $shippingCost = 0;
        }

        // Check Threshold-based Special Offer
        $thresholdDiscount = 0;
        if (!empty($ecomSettings['threshold_offer_enabled'])) {
            $reqAmount = floatval($ecomSettings['threshold_offer_amount'] ?? 1000);
            if ($subtotal >= $reqAmount) {
                $offerType = $ecomSettings['threshold_offer_type'] ?? 'free_delivery';
                $offerVal = floatval($ecomSettings['threshold_offer_discount'] ?? 0);
                if ($offerType === 'free_delivery') {
                    $shippingCost = 0;
                } elseif ($offerType === 'flat_discount') {
                    $thresholdDiscount = min($subtotal, $offerVal);
                } elseif ($offerType === 'percent_discount') {
                    $thresholdDiscount = round(($subtotal * $offerVal) / 100);
                }
            }
        }

        // Check Coupon Code Discount
        $couponDiscount = 0;
        $appliedCoupon = null;
        if (!empty($validated['coupon_code']) && !empty($ecomSettings['coupon_enabled'])) {
            $inputCode = strtoupper(trim($validated['coupon_code']));
            $configCode = strtoupper(trim($ecomSettings['coupon_code'] ?? 'IDEA2026'));
            $minOrder = floatval($ecomSettings['coupon_min_order'] ?? 500);

            if ($inputCode === $configCode && $subtotal >= $minOrder) {
                $cType = $ecomSettings['coupon_type'] ?? 'percent';
                $cDisc = floatval($ecomSettings['coupon_discount'] ?? 10);
                $couponDiscount = ($cType === 'percent')
                    ? round(($subtotal * $cDisc) / 100)
                    : min($subtotal, $cDisc);
                $appliedCoupon = $configCode;
            }
        }

        $totalDiscount = $couponDiscount + $thresholdDiscount;

        $isGift = !empty($validated['is_gift']);
        $giftFee = $isGift ? $giftWrapFee : 0;
        $totalAmount = max(0, $subtotal - $totalDiscount) + $shippingCost + $giftFee;

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

        $adminNoteParts = [];
        $adminNoteParts[] = 'অর্ডার আইটেম: ' . implode(', ', $titles);
        if ($appliedCoupon) {
            $adminNoteParts[] = "কুপন কোড: {$appliedCoupon} (ছাড়: ৳{$couponDiscount})";
        }
        if ($thresholdDiscount > 0) {
            $adminNoteParts[] = "বিশেষ অফার ছাড়: ৳{$thresholdDiscount}";
        }

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
            'unit_price'             => $totalQuantity > 0 ? round($subtotal / $totalQuantity, 2) : $subtotal,
            'shipping_cost'          => $shippingCost,
            'discount_amount'        => $totalDiscount,
            'gift_wrap_fee'          => $giftFee,
            'total_amount'           => $totalAmount,
            'payment_method'         => $paymentMethod,
            'payment_status'         => $paymentStatus,
            'transaction_id'         => $validated['transaction_id'] ?? null,
            'payment_phone'          => $validated['payment_phone'] ?? null,
            'status'                 => 'pending',
            'admin_notes'            => implode(' | ', $adminNoteParts),
            'points_earned'          => $pointsEarned,
            'affiliate_id'           => $affiliateId,
            'commission_amount'      => $commissionAmount,
        ]);

        if (auth()->check()) {
            auth()->user()->increment('loyalty_points', $pointsEarned);
        }

        // Process e-book royalty split and user library access if applicable
        \App\Services\RoyaltyService::processOrderRoyalties($order);

        // Check if automated PGW redirection is active
        $gwSettings = [];
        if (Schema::hasTable('admin_dashboard_settings')) {
            $gwRow = AdminDashboardSetting::where('key', 'payment_gateways')->first();
            $gwSettings = $gwRow?->value ?? [];
        }

        if ($paymentMethod === 'card' && !empty($gwSettings['sslcommerz']['enabled'])) {
            $payReq = new Request(['order_id' => $order->id]);
            return app(PaymentController::class)->createSslcommerzPayment($payReq);
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

