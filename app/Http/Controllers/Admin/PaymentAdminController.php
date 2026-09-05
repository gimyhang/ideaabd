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
use Illuminate\Support\Facades\Storage;

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

        $savedGateways = $settings['payment_gateways'] ?? [];

        // Comprehensive defaults with full 3-mode and multi-gateway support
        $paymentGateways = array_replace_recursive([
            'bkash' => [
                'enabled'      => true,
                'name'         => 'বিকাশ (bKash)',
                'mode'         => 'manual', // manual, automated, custom_code
                'number'       => $ecomSetting['bkash_number'] ?? '01558712810',
                'type'         => 'personal', // personal, merchant, agent
                'fee_percent'  => 0,
                'instructions' => 'বিকাশ অ্যাপ থেকে Send Money অপশনে গিয়ে উল্লেখিত নম্বরে বিল পাঠিয়ে TrxID ও নম্বর দিন।',
                'qr_code'      => null,
                'app_key'      => '',
                'app_secret'   => '',
                'username'     => '',
                'password'     => '',
                'sandbox'      => '0',
                'custom_code'  => '',
            ],
            'nagad' => [
                'enabled'         => true,
                'name'            => 'নগদ (Nagad)',
                'mode'            => 'manual',
                'number'          => $ecomSetting['nagad_number'] ?? '01558712810',
                'type'            => 'personal',
                'fee_percent'     => 0,
                'instructions'    => 'নগদ অ্যাপ থেকে Send Money অপশনে গিয়ে উল্লেখিত নম্বরে বিল পাঠিয়ে TrxID ও নম্বর দিন।',
                'qr_code'         => null,
                'merchant_id'     => '',
                'merchant_number' => '',
                'public_key'      => '',
                'private_key'     => '',
                'sandbox'         => '0',
                'custom_code'     => '',
            ],
            'rocket' => [
                'enabled'      => false,
                'name'         => 'রকেট (Rocket - DBBL)',
                'mode'         => 'manual',
                'number'       => $ecomSetting['rocket_number'] ?? '01558712810',
                'type'         => 'personal',
                'fee_percent'  => 0,
                'instructions' => 'রকেট একাউন্ট থেকে সেন্ড মানি করে ট্রানজাকশন আইডি (TrxID) দিন।',
                'qr_code'      => null,
                'custom_code'  => '',
            ],
            'upay' => [
                'enabled'      => false,
                'name'         => 'উপায় (Upay - UCB)',
                'mode'         => 'manual',
                'number'       => '01558712810',
                'type'         => 'personal',
                'fee_percent'  => 0,
                'instructions' => 'উপায় একাউন্ট থেকে সেন্ড মানি করে ট্রানজাকশন আইডি দিন।',
                'qr_code'      => null,
                'custom_code'  => '',
            ],
            'cellfin' => [
                'enabled'      => false,
                'name'         => 'সেলফিন ও ব্যাংক (Cellfin / IBBL)',
                'mode'         => 'manual',
                'number'       => '01726976982',
                'instructions' => 'সেলফিন অ্যাপের Fund Transfer বা সেন্ড মানি করে ট্রানজাকশন রেফারেন্স দিন।',
                'qr_code'      => null,
                'custom_code'  => '',
            ],
            'sslcommerz' => [
                'enabled'      => false,
                'name'         => 'SSLCommerz (কার্ড ও নেট ব্যাংকিং)',
                'store_id'     => '',
                'store_passwd' => '',
                'sandbox'      => '0',
                'instructions' => 'ভিসা, মাস্টারকার্ড, অ্যামেক্স অথবা অনলাইন ব্যাংকিং-এর মাধ্যমে নিরাপদে পেমেন্ট সম্পন্ন করুন।',
            ],
            'shurjopay' => [
                'enabled'           => false,
                'name'              => 'সূর্যপে (ShurjoPay Gateway)',
                'merchant_username' => '',
                'merchant_password' => '',
                'prefix'            => 'IDEA',
                'sandbox'           => '0',
                'instructions'      => 'Shurjopay-এর মাধ্যমে ডেবিট/ক্রেডিট কার্ড ও ইন্টারনেট ব্যাংকিং দিয়ে বিল পরিশোধ করুন।',
            ],
            'aamarpay' => [
                'enabled'       => false,
                'name'          => 'আমারপে (AamarPay Gateway)',
                'store_id'      => '',
                'signature_key' => '',
                'sandbox'       => '0',
                'instructions'  => 'AamarPay সিকিউর গেটওয়ের মাধ্যমে তাৎক্ষণিক লেনদেন সম্পন্ন করুন।',
            ],
            'bank' => [
                'enabled'      => false,
                'name'         => 'ব্যাংক ওয়্যার ট্রান্সফার (Bank Deposit)',
                'bank_name'    => 'Islami Bank Bangladesh Ltd',
                'account_name' => 'Idea Prokashon',
                'account_no'   => '2050XXXXXXXXX',
                'branch'       => 'Rangpur Branch',
                'routing'      => '125XXXXXXXX',
                'swift_code'   => '',
                'instructions' => 'ব্যাংক অ্যাকাউন্টে টাকা পাঠিয়ে ডিপোজিট স্লিপ বা রেফারেন্স নম্বর দিন।',
                'qr_code'      => null,
            ],
            'cod' => [
                'enabled'                 => true,
                'name'                    => 'ক্যাশ অন ডেলিভারি (COD)',
                'advance_charge_required' => false,
                'advance_charge_amount'   => 0,
                'instructions'            => 'বই হাতে পেয়ে ডেলিভারি ম্যানের কাছে মূল্য পরিশোধ করুন।',
            ],
            'global_scripts' => [
                'header_script'   => '',
                'footer_script'   => '',
                'checkout_notice' => 'নিরাপদ লেনদেনের জন্য কোনো সমস্যা হলে আমাদের হেল্পলাইনে (01726-976982) কল করুন।',
            ],
        ], $savedGateways);

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
     * Update Payment Gateways configuration & QR codes.
     */
    public function updateGateways(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'payment_gateways' => 'required|array',
            'qr_codes'         => 'nullable|array',
        ]);

        $gateways = $validated['payment_gateways'];

        // Handle QR Code image uploads / base64 for each gateway
        $qrCodes = $request->file('qr_codes', []);
        $existingGateways = [];
        if (Schema::hasTable('admin_dashboard_settings')) {
            $row = AdminDashboardSetting::where('key', 'payment_gateways')->first();
            $existingGateways = $row?->value ?? [];
        }

        foreach (['bkash', 'nagad', 'rocket', 'upay', 'cellfin', 'bank'] as $gw) {
            // Check if a new file was uploaded
            if (isset($qrCodes[$gw]) && $qrCodes[$gw]->isValid()) {
                $path = \App\Services\ImageOptimizerService::convertAndStore($qrCodes[$gw], 'settings/qrcodes', 'public', 88, 1200, 1200);
                $gateways[$gw]['qr_code'] = 'storage/' . $path;
            } elseif ($request->filled("qr_base64.{$gw}")) {
                // Base64 cropped image
                $base64 = $request->input("qr_base64.{$gw}");
                $saved = $this->saveBase64Image($base64, 'settings/qrcodes');
                if ($saved) {
                    $gateways[$gw]['qr_code'] = $saved;
                }
            } elseif (!empty($existingGateways[$gw]['qr_code'])) {
                // Preserve existing QR code if not cleared
                if ($request->boolean("remove_qr.{$gw}")) {
                    $gateways[$gw]['qr_code'] = null;
                } else {
                    $gateways[$gw]['qr_code'] = $existingGateways[$gw]['qr_code'];
                }
            }
        }

        if (Schema::hasTable('admin_dashboard_settings')) {
            AdminDashboardSetting::updateOrCreate(
                ['key' => 'payment_gateways'],
                ['value' => $gateways, 'updated_by' => auth()->id()]
            );

            // Sync phone numbers with ecommerce_settings if relevant
            $ecomRow = AdminDashboardSetting::where('key', 'ecommerce_settings')->first();
            $ecomData = $ecomRow?->value ?? [];

            if (isset($gateways['bkash']['number'])) {
                $ecomData['bkash_number'] = $gateways['bkash']['number'];
            }
            if (isset($gateways['nagad']['number'])) {
                $ecomData['nagad_number'] = $gateways['nagad']['number'];
            }
            if (isset($gateways['rocket']['number'])) {
                $ecomData['rocket_number'] = $gateways['rocket']['number'];
            }

            AdminDashboardSetting::updateOrCreate(
                ['key' => 'ecommerce_settings'],
                ['value' => $ecomData, 'updated_by' => auth()->id()]
            );

            \App\Support\SiteSetting::clearCache();
        }

        if ($this->accessService) {
            $this->accessService->log('update_payment_gateways', 'পেমেন্ট গেটওয়ে, লাইভ এপিআই ও কাস্টম কোড সেটিংস আপডেট করা হয়েছে');
        }

        return back()->with('success', 'পেমেন্ট গেটওয়ে, লাইভ এপিআই ও কাস্টম কোড সেটিংস সফলভাবে সংরক্ষণ করা হয়েছে!');
    }

    /**
     * Save base64 data to storage.
     */
    private function saveBase64Image(?string $base64Data, string $folder): ?string
    {
        if (!$base64Data || !str_starts_with($base64Data, 'data:image/')) {
            return null;
        }

        $path = \App\Services\ImageOptimizerService::convertBase64AndStore($base64Data, $folder, 'public', 88, 1200, 1200);
        return $path ? 'storage/' . $path : null;
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
