<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class PaymentController extends Controller
{
    /* =========================================================================
     | ১. বিকাশ (bKash Tokenized API Integration)
     | ========================================================================= */

    /**
     * Get bKash configuration from DB settings or config
     */
    private function getBkashConfig(): array
    {
        $settings = [];
        if (\Illuminate\Support\Facades\Schema::hasTable('admin_dashboard_settings')) {
            $row = \App\Models\AdminDashboardSetting::where('key', 'payment_gateways')->first();
            $settings = $row?->value['bkash'] ?? [];
        }

        $isSandbox = ($settings['sandbox'] ?? env('BKASH_SANDBOX', true)) == '1' || ($settings['sandbox'] ?? env('BKASH_SANDBOX', true)) === true;

        return [
            'app_key'    => $settings['app_key'] ?? config('services.bkash.app_key', env('BKASH_APP_KEY', '')),
            'app_secret' => $settings['app_secret'] ?? config('services.bkash.app_secret', env('BKASH_APP_SECRET', '')),
            'username'   => $settings['username'] ?? config('services.bkash.username', env('BKASH_USERNAME', '')),
            'password'   => $settings['password'] ?? config('services.bkash.password', env('BKASH_PASSWORD', '')),
            'sandbox'    => $isSandbox,
            'base_url'   => $isSandbox
                ? 'https://tokenized.sandbox.bka.sh/v1.2.0-beta'
                : 'https://tokenized.pay.bka.sh/v1.2.0-beta',
        ];
    }

    /**
     * বিকাশের অ্যাথেন্টিকেশন টোকেন জেনারেট করে
     */
    private function getBkashToken()
    {
        $config = $this->getBkashConfig();

        $response = Http::withHeaders([
            'username' => $config['username'],
            'password' => $config['password'],
        ])->post($config['base_url'] . '/checkout/token/grant', [
            'app_key'    => $config['app_key'],
            'app_secret' => $config['app_secret'],
        ]);

        if ($response->successful() && isset($response->json()['id_token'])) {
            return $response->json()['id_token'];
        }

        Log::channel('json')->error('bKash Token Generation Failed', (array) $response->json());
        throw new Exception('bKash authentication failed.');
    }

    /**
     * বিকাশ পেমেন্ট ইনিশিয়েট বা ক্রিয়েট করে
     */
    public function createBkashPayment(Request $request)
    {
        $request->validate([
            'order_id' => 'required|exists:orders,id',
        ]);

        $order = Order::findOrFail($request->order_id);
        $config = $this->getBkashConfig();

        try {
            $token = $this->getBkashToken();

            $response = Http::withHeaders([
                'Authorization' => $token,
                'X-APP-Key'     => $config['app_key'],
            ])->post($config['base_url'] . '/checkout/create', [
                'mode'                  => '0011',
                'payerReference'        => $order->phone ?? '01700000000',
                'callbackURL'           => route('bkash.callback'),
                'amount'                => number_format($order->total_amount, 2, '.', ''),
                'currency'              => 'BDT',
                'intent'                => 'sale',
                'merchantInvoiceNumber' => $order->order_number,
            ]);

            $resData = $response->json();

            if (isset($resData['paymentID']) && isset($resData['bkashURL'])) {
                // ডাটাবেজে বিকাশ পেমেন্ট আইডি সেভ রাখা
                $order->update(['payment_id' => $resData['paymentID']]);

                return response()->json([
                    'status'       => 'success',
                    'redirect_url' => $resData['bkashURL'],
                ]);
            }

            return response()->json(['status' => 'error', 'message' => $resData['statusMessage'] ?? 'bKash initiation failed'], 400);

        } catch (Exception $e) {
            Log::channel('json')->error('bKash Create Payment Error', ['error' => $e->getMessage()]);
            return response()->json(['status' => 'error', 'message' => 'Payment initialization failed.'], 500);
        }
    }

    /**
     * বিকাশ পেমেন্ট এক্সিকিউট বা কনফার্মেশন হ্যান্ডলার (Callback)
     */
    public function bkashCallback(Request $request)
    {
        $paymentID = $request->input('paymentID');
        $status    = $request->input('status');
        $config    = $this->getBkashConfig();

        if ($status === 'success') {
            try {
                $token = $this->getBkashToken();

                $response = Http::withHeaders([
                    'Authorization' => $token,
                    'X-APP-Key'     => $config['app_key'],
                ])->post($config['base_url'] . '/checkout/execute', [
                    'paymentID' => $paymentID,
                ]);

                $resData = $response->json();

                if (isset($resData['transactionStatus']) && $resData['transactionStatus'] === 'Completed') {
                    $order = Order::where('payment_id', $paymentID)->firstOrFail();
                    $order->update([
                        'status'         => 'paid',
                        'transaction_id' => $resData['trxID'],
                        'payment_method' => 'bKash',
                    ]);

                    // সিকিউরিটি ও ইনভেন্টরি লগ
                    Log::channel('audit')->info('bKash Payment Successful', [
                        'order_id' => $order->id,
                        'trx_id'   => $resData['trxID'],
                        'amount'   => $resData['amount'],
                    ]);

                    \App\Services\RoyaltyService::processOrderRoyalties($order);

                    return redirect()->route('payment.success')->with('message', 'বিকাশ পেমেন্ট সফল হয়েছে!');
                }
            } catch (Exception $e) {
                Log::channel('json')->error('bKash Execute Payment Exception', ['error' => $e->getMessage()]);
            }
        }

        return redirect()->route('payment.fail')->with('error', 'বিকাশ পেমেন্ট ব্যর্থ বা বাতিল করা হয়েছে।');
    }


    /* =========================================================================
     | ২. নগদ (Nagad Payment Gateway Integration)
     | ========================================================================= */

    /**
     * Get Nagad configuration from DB settings or config
     */
    private function getNagadConfig(): array
    {
        $settings = [];
        if (\Illuminate\Support\Facades\Schema::hasTable('admin_dashboard_settings')) {
            $row = \App\Models\AdminDashboardSetting::where('key', 'payment_gateways')->first();
            $settings = $row?->value['nagad'] ?? [];
        }

        $isSandbox = ($settings['sandbox'] ?? env('NAGAD_SANDBOX', true)) == '1' || ($settings['sandbox'] ?? env('NAGAD_SANDBOX', true)) === true;

        return [
            'merchant_id'     => $settings['merchant_id'] ?? config('services.nagad.merchant_id', env('NAGAD_MERCHANT_ID', '')),
            'merchant_number' => $settings['merchant_number'] ?? config('services.nagad.merchant_number', env('NAGAD_MERCHANT_NUMBER', '')),
            'public_key'      => $settings['public_key'] ?? config('services.nagad.public_key', env('NAGAD_PUBLIC_KEY', '')),
            'private_key'     => $settings['private_key'] ?? config('services.nagad.private_key', env('NAGAD_PRIVATE_KEY', '')),
            'sandbox'         => $isSandbox,
            'base_url'        => $isSandbox
                ? 'http://sandbox.mynagad.com:10080/remote-payment-gateway-1.0/api/dfs'
                : 'https://api.mynagad.com/api/dfs',
        ];
    }

    /**
     * নগদ পেমেন্ট ইনিশিয়েট করার প্রক্রিয়া
     */
    public function createNagadPayment(Request $request)
    {
        $request->validate([
            'order_id' => 'required|exists:orders,id',
        ]);

        $order      = Order::findOrFail($request->order_id);
        $config     = $this->getNagadConfig();
        $merchantID = $config['merchant_id'] ?: '683002007104225';
        $dateTime   = now()->format('YmdHis');
        $orderId    = 'ORD' . $order->id . '-' . rand(100, 999);

        $sensitiveData = [
            'merchantId' => $merchantID,
            'datetime'   => $dateTime,
            'orderId'    => $orderId,
            'challenge'  => \Illuminate\Support\Str::random(40),
        ];

        try {
            $order->update([
                'payment_id'     => $orderId,
                'payment_method' => 'Nagad',
            ]);

            Log::channel('audit')->info('Nagad Payment Initiated', ['order_id' => $order->id, 'nagad_order_id' => $orderId]);

            return response()->json([
                'status'       => 'success',
                'message'      => 'Nagad Payment Gateway URL ready.',
                'redirect_url' => $config['base_url'] . '/check-out/' . $orderId,
            ]);

        } catch (Exception $e) {
            Log::channel('json')->error('Nagad Payment Initiation Error', ['error' => $e->getMessage()]);
            return response()->json(['status' => 'error', 'message' => 'Nagad initiation failed.'], 500);
        }
    }

    /**
     * নগদ পেমেন্ট সফল হলে রিটার্ন ব্যাক হ্যান্ডলার
     */
    public function nagadCallback(Request $request)
    {
        $status  = $request->input('status_code');
        $order_id = $request->input('order_id');

        if ($status === '00_0000') { // নগদের সাকসেস স্ট্যাটাস কোড
            $order = Order::where('payment_id', $order_id)->first();

            if ($order) {
                $order->update([
                    'payment_status' => 'paid',
                    'status'         => 'processing',
                    'transaction_id' => $request->input('payment_ref_id'),
                ]);

                Log::channel('audit')->info('Nagad Payment Successful', [
                    'order_id' => $order->id,
                    'trx_id'   => $request->input('payment_ref_id'),
                ]);

                \App\Services\RoyaltyService::processOrderRoyalties($order);

                return redirect()->route('payment.success')->with([
                    'message'      => 'নগদ পেমেন্ট সফল হয়েছে!',
                    'order_number' => $order->order_number,
                ]);
            }
        }

        return redirect()->route('payment.fail')->with('error', 'নগদ পেমেন্ট ব্যর্থ বা বাতিল করা হয়েছে।');
    }


    /* =========================================================================
     | ৩. এসএসএলকমার্জ (SSLCommerz Automated Cards & Banking Gateway)
     | ========================================================================= */

    /**
     * Get SSLCommerz configuration from DB settings or config
     */
    private function getSslcommerzConfig(): array
    {
        $settings = [];
        if (\Illuminate\Support\Facades\Schema::hasTable('admin_dashboard_settings')) {
            $row = \App\Models\AdminDashboardSetting::where('key', 'payment_gateways')->first();
            $settings = $row?->value['sslcommerz'] ?? [];
        }

        $isSandbox = ($settings['sandbox'] ?? env('SSLCOMMERZ_SANDBOX', true)) == '1' || ($settings['sandbox'] ?? env('SSLCOMMERZ_SANDBOX', true)) === true;
        
        return [
            'store_id'     => $settings['store_id'] ?? env('SSLCOMMERZ_STORE_ID', ''),
            'store_passwd' => $settings['store_passwd'] ?? env('SSLCOMMERZ_STORE_PASSWORD', ''),
            'sandbox'      => $isSandbox,
            'api_url'      => $isSandbox
                ? 'https://sandbox.sslcommerz.com/gwprocess/v4/api.php'
                : 'https://securepay.sslcommerz.com/gwprocess/v4/api.php',
            'validate_url' => $isSandbox
                ? 'https://sandbox.sslcommerz.com/validator/api/validationserverAPI.php'
                : 'https://securepay.sslcommerz.com/validator/api/validationserverAPI.php',
        ];
    }

    /**
     * SSLCommerz পেমেন্ট ইনিশিয়েট করে গেটওয়ে ইউআরএলে রিডাইরেক্ট করে
     */
    public function createSslcommerzPayment(Request $request)
    {
        $request->validate([
            'order_id' => 'required|exists:orders,id',
        ]);

        $order = Order::findOrFail($request->order_id);
        $config = $this->getSslcommerzConfig();

        if (empty($config['store_id']) || empty($config['store_passwd'])) {
            return back()->with('error', 'SSLCommerz পেমেন্ট গেটওয়ে কনফিগারেশন সেট করা নেই। অ্যাডমিন প্যানেল থেকে কনফিগার করুন।');
        }

        $postData = [
            'store_id'         => $config['store_id'],
            'store_passwd'     => $config['store_passwd'],
            'total_amount'     => number_format($order->total_amount, 2, '.', ''),
            'currency'         => 'BDT',
            'tran_id'          => $order->order_number ?? ('IDP-' . $order->id . '-' . time()),
            'success_url'      => route('sslcommerz.success'),
            'fail_url'         => route('sslcommerz.fail'),
            'cancel_url'       => route('sslcommerz.cancel'),
            'ipn_url'          => route('sslcommerz.ipn'),
            
            // Customer Information
            'cus_name'         => $order->customer_name ?? 'Customer',
            'cus_email'        => $order->customer_email ?? 'customer@ideaabd.com',
            'cus_add1'         => $order->customer_address ?? 'Bangladesh',
            'cus_city'         => $order->district_label ?? 'Dhaka',
            'cus_country'      => 'Bangladesh',
            'cus_phone'        => $order->customer_phone ?? '01700000000',

            // Shipment Information
            'shipping_method'  => 'Courier',
            'num_of_item'      => $order->quantity ?? 1,
            'product_name'     => $order->book->title ?? 'Idea Publication Books',
            'product_category' => 'Books',
            'product_profile'  => 'physical-goods',
        ];

        try {
            $response = Http::asForm()->post($config['api_url'], $postData);
            $sslcz = $response->json();

            if (!empty($sslcz['GatewayPageURL'])) {
                $order->update(['payment_id' => $postData['tran_id']]);
                return redirect()->away($sslcz['GatewayPageURL']);
            }

            return back()->with('error', 'SSLCommerz সেশন তৈরিতে সমস্যা হয়েছে: ' . ($sslcz['failedreason'] ?? 'Please try again later.'));

        } catch (Exception $e) {
            Log::channel('json')->error('SSLCommerz Initialization Exception', ['error' => $e->getMessage()]);
            return back()->with('error', 'অনলাইন পেমেন্ট গেটওয়ে চালু করা যায়নি: ' . $e->getMessage());
        }
    }

    /**
     * SSLCommerz পেমেন্ট সফল হওয়ার পর হ্যান্ডলার
     */
    public function sslcommerzSuccess(Request $request)
    {
        $tran_id = $request->input('tran_id');
        $val_id = $request->input('val_id');
        $amount = $request->input('amount');
        $card_type = $request->input('card_type');

        $order = Order::where('order_number', $tran_id)->orWhere('payment_id', $tran_id)->first();

        if ($order) {
            $order->update([
                'payment_status' => 'paid',
                'status'         => 'processing',
                'payment_method' => 'card',
                'transaction_id' => $val_id ?? $request->input('bank_tran_id', $tran_id),
                'admin_notes'    => trim(($order->admin_notes ?? '') . " | Card: {$card_type}"),
            ]);

            \App\Services\RoyaltyService::processOrderRoyalties($order);

            return redirect()->route('payment.success')->with([
                'message'      => "আপনার কার্ড/অনলাইন পেমেন্টটি সফল হয়েছে! (পদ্ধতি: {$card_type})",
                'order_number' => $order->order_number,
            ]);
        }

        return redirect()->route('payment.success')->with('message', 'পেমেন্ট সফলভাবে সম্পন্ন হয়েছে!');
    }

    public function sslcommerzFail(Request $request)
    {
        $tran_id = $request->input('tran_id');
        $order = Order::where('order_number', $tran_id)->orWhere('payment_id', $tran_id)->first();
        if ($order) {
            $order->update(['payment_status' => 'unpaid']);
        }

        return redirect()->route('payment.fail')->with('error', 'অনলাইন কার্ড পেমেন্ট সম্পন্ন করা যায়নি।');
    }

    public function sslcommerzCancel(Request $request)
    {
        return redirect()->route('payment.fail')->with('error', 'আপনি অনলাইন পেমেন্টটি বাতিল করেছেন।');
    }

    public function sslcommerzIpn(Request $request)
    {
        $tran_id = $request->input('tran_id');
        $status = $request->input('status');

        if ($status === 'VALID' || $status === 'VALIDATED') {
            $order = Order::where('order_number', $tran_id)->orWhere('payment_id', $tran_id)->first();
            if ($order) {
                $order->update([
                    'payment_status' => 'paid',
                    'status'         => 'processing',
                    'transaction_id' => $request->input('val_id'),
                ]);
                \App\Services\RoyaltyService::processOrderRoyalties($order);
            }
        }

        return response()->json(['status' => 'IPN Processed']);
    }


    /* =========================================================================
     | ৪. সার্বজনীন রেসপন্স ভিউ
     | ========================================================================= */

    public function success()
    {
        return view('payment.success');
    }

    public function fail()
    {
        return view('payment.fail');
    }
}