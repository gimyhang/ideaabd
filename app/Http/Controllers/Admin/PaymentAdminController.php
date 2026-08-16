<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AdminDashboardSetting;
use App\Models\Order;
use App\Services\AdminAccessService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;

class PaymentAdminController extends Controller
{
    public function __construct(private readonly ?AdminAccessService $accessService = null)
    {
    }

    /**
     * Display Payment Gateways settings and payment transaction logs.
     */
    public function index(Request $request): View
    {
        // 1. Fetch Payment Gateways & Ecommerce Settings
        $settings = [];
        if (Schema::hasTable('admin_dashboard_settings')) {
            $settings = AdminDashboardSetting::all()->pluck('value', 'key')->toArray();
        }

        $ecomSetting = $settings['ecommerce_settings'] ?? [
            'bkash_number'  => '01558712810',
            'nagad_number'  => '01558712810',
            'rocket_number' => '01558712810',
        ];

        $paymentGateways = $settings['payment_gateways'] ?? [
            'bkash' => [
                'enabled'      => true,
                'name'         => 'বিকাশ (bKash)',
                'number'       => $ecomSetting['bkash_number'] ?? '01558712810',
                'type'         => 'personal',
                'instructions' => 'বিকাশ অ্যাপ থেকে Send Money অপশনে গিয়ে উল্লেখিত নম্বরে বিল পাঠিয়ে TrxID ও নম্বর দিন।',
            ],
            'nagad' => [
                'enabled'      => true,
                'name'         => 'নগদ (Nagad)',
                'number'       => $ecomSetting['nagad_number'] ?? '01558712810',
                'type'         => 'personal',
                'instructions' => 'নগদ অ্যাপ থেকে Send Money অপশনে গিয়ে উল্লেখিত নম্বরে বিল পাঠিয়ে TrxID ও নম্বর দিন।',
            ],
            'rocket' => [
                'enabled'      => false,
                'name'         => 'রকেট (Rocket)',
                'number'       => $ecomSetting['rocket_number'] ?? '01558712810',
                'type'         => 'personal',
                'instructions' => 'রকেট একাউন্ট থেকে সেন্ড মানি করে ট্রানজাকশন আইডি দিন।',
            ],
            'upay' => [
                'enabled'      => false,
                'name'         => 'উপায় (Upay)',
                'number'       => '01558712810',
                'type'         => 'personal',
                'instructions' => 'উপায় একাউন্ট থেকে সেন্ড মানি করুন।',
            ],
            'cod' => [
                'enabled'      => true,
                'name'         => 'ক্যাশ অন ডেলিভারি (COD)',
                'instructions' => 'বই হাতে পেয়ে মূল্য পরিশোধ করার সুবিধা।',
            ],
            'bank' => [
                'enabled'      => false,
                'bank_name'    => 'Islami Bank Bangladesh Ltd',
                'account_name' => 'Idea Prokashon',
                'account_no'   => '2050XXXXXXXXX',
                'branch'       => 'Rangpur Branch',
                'routing'      => '125XXXXXXXX',
                'instructions' => 'ব্যাংক অ্যাকাউন্টে টাকা পাঠিয়ে ডিপোজিট স্লিপ বা রেফারেন্স নম্বর দিন।',
            ],
        ];

        // 2. Fetch Payment Transactions / Orders with filters
        $ordersQuery = Order::query()->with('book');
        $hasPaymentMethod = Schema::hasColumn('orders', 'payment_method');
        $hasPaymentStatus = Schema::hasColumn('orders', 'payment_status');

        if ($request->filled('method') && $hasPaymentMethod) {
            $ordersQuery->where('payment_method', $request->string('method'));
        }

        if ($request->filled('status') && $hasPaymentStatus) {
            $ordersQuery->where('payment_status', $request->string('status'));
        }

        if ($request->filled('search')) {
            $term = '%' . $request->string('search')->trim() . '%';
            $ordersQuery->where(function ($q) use ($term) {
                $hasOrderNumber = Schema::hasColumn('orders', 'order_number');
                $hasTrx = Schema::hasColumn('orders', 'transaction_id');
                $hasPayPhone = Schema::hasColumn('orders', 'payment_phone');

                $q->where('customer_phone', 'like', $term)
                  ->orWhere('customer_name', 'like', $term);

                if ($hasOrderNumber) {
                    $q->orWhere('order_number', 'like', $term);
                }
                if ($hasTrx) {
                    $q->orWhere('transaction_id', 'like', $term);
                }
                if ($hasPayPhone) {
                    $q->orWhere('payment_phone', 'like', $term);
                }
            });
        }

        $transactions = $ordersQuery->latest()->paginate(20)->withQueryString();

        // 3. Metric summaries with defensive schema checks
        $stats = [
            'total_online_revenue' => $hasPaymentStatus ? (float) Order::where('payment_status', 'paid')->sum('total_amount') : 0.0,
            'paid_orders_count'    => $hasPaymentStatus ? (int) Order::where('payment_status', 'paid')->count() : 0,
            'pending_orders_count' => $hasPaymentStatus ? (int) Order::where('payment_status', 'pending')->count() : 0,
            'bkash_revenue'        => ($hasPaymentMethod && $hasPaymentStatus) ? (float) Order::where('payment_method', 'bkash')->where('payment_status', 'paid')->sum('total_amount') : 0.0,
            'nagad_revenue'        => ($hasPaymentMethod && $hasPaymentStatus) ? (float) Order::where('payment_method', 'nagad')->where('payment_status', 'paid')->sum('total_amount') : 0.0,
            'cod_revenue'          => ($hasPaymentMethod && $hasPaymentStatus) ? (float) Order::where('payment_method', 'cod')->where('payment_status', 'paid')->sum('total_amount') : 0.0,
        ];

        return view('admin.payments.index', compact('paymentGateways', 'transactions', 'stats'));
    }

    /**
     * Update Payment Gateways configuration.
     */
    public function updateGateways(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'payment_gateways' => 'required|array',
        ]);

        if (Schema::hasTable('admin_dashboard_settings')) {
            AdminDashboardSetting::updateOrCreate(
                ['key' => 'payment_gateways'],
                ['value' => $validated['payment_gateways']]
            );

            // Sync phone numbers with ecommerce_settings if relevant
            $ecomRow = AdminDashboardSetting::where('key', 'ecommerce_settings')->first();
            $ecomData = $ecomRow?->value ?? [];
            
            if (isset($validated['payment_gateways']['bkash']['number'])) {
                $ecomData['bkash_number'] = $validated['payment_gateways']['bkash']['number'];
            }
            if (isset($validated['payment_gateways']['nagad']['number'])) {
                $ecomData['nagad_number'] = $validated['payment_gateways']['nagad']['number'];
            }
            if (isset($validated['payment_gateways']['rocket']['number'])) {
                $ecomData['rocket_number'] = $validated['payment_gateways']['rocket']['number'];
            }

            AdminDashboardSetting::updateOrCreate(
                ['key' => 'ecommerce_settings'],
                ['value' => $ecomData]
            );
        }

        if ($this->accessService) {
            $this->accessService->log('update_payment_gateways', 'পেমেন্ট গেটওয়ে সেটিংস আপডেট করা হয়েছে');
        }

        return back()->with('success', 'পেমেন্ট গেটওয়ে সেটিংস সফলভাবে সংরক্ষণ করা হয়েছে!');
    }

    /**
     * Quick update payment status of an order / transaction.
     */
    public function updateStatus(Request $request, Order $order): RedirectResponse
    {
        $validated = $request->validate([
            'payment_status' => 'required|string|in:paid,pending,failed,refunded',
            'transaction_id' => 'nullable|string|max:100',
        ]);

        $order->update($validated);

        if ($this->accessService) {
            $this->accessService->log(
                'update_order_payment',
                "অর্ডার #{$order->order_number}-এর পেমেন্ট স্ট্যাটাস '{$validated['payment_status']}' করা হয়েছে"
            );
        }

        return back()->with('success', "অর্ডার #{$order->order_number}-এর পেমেন্ট তথ্য আপডেট করা হয়েছে!");
    }
}
