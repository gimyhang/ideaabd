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
     * বিকাশের অ্যাথেন্টিকেশন টোকেন জেনারেট করে
     */
    private function getBkashToken()
    {
        $response = Http::withHeaders([
            'username' => config('services.bkash.username'),
            'password' => config('services.bkash.password'),
        ])->post(config('services.bkash.base_url') . '/checkout/token/grant', [
            'app_key'    => config('services.bkash.app_key'),
            'app_secret' => config('services.bkash.app_secret'),
        ]);

        if ($response->successful() && isset($response->json()['id_token'])) {
            return $response->json()['id_token'];
        }

        Log::channel('json')->error('bKash Token Generation Failed', $response->json());
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

        try {
            $token = $this->getBkashToken();

            $response = Http::withHeaders([
                'Authorization' => $token,
                'X-APP-Key'     => config('services.bkash.app_key'),
            ])->post(config('services.bkash.base_url') . '/checkout/create', [
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

        if ($status === 'success') {
            try {
                $token = $this->getBkashToken();

                $response = Http::withHeaders([
                    'Authorization' => $token,
                    'X-APP-Key'     => config('services.bkash.app_key'),
                ])->post(config('services.bkash.base_url') . '/checkout/execute', [
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
     * নগদ পেমেন্ট ইনিশিয়েট করার প্রক্রিয়া
     */
    public function createNagadPayment(Request $request)
    {
        $request->validate([
            'order_id' => 'required|exists:orders,id',
        ]);

        $order      = Order::findOrFail($request->order_id);
        $merchantID = config('services.nagad.merchant_id');
        $dateTime   = now()->format('YmdHis');
        $orderId    = 'ORD' . $order->id . '-' . rand(100, 999);

        $sensitiveData = [
            'merchantId' => $merchantID,
            'datetime'   => $dateTime,
            'orderId'    => $orderId,
            'challenge'  => Str::random(40),
        ];

        // নগদ পেমেন্টের জন্য রিকোয়েস্ট প্লেসহোল্ডার (নগদের দেওয়া স্যান্ডবক্স/লাইভ এন্ডপয়েন্টে পাঠানো হয়)
        try {
            // ডাটাবেজে অর্ডারের রেফারেন্স আপডেট
            $order->update([
                'payment_id'     => $orderId,
                'payment_method' => 'Nagad',
            ]);

            // অডিট লগে এন্ট্রি
            Log::channel('audit')->info('Nagad Payment Initiated', ['order_id' => $order->id, 'nagad_order_id' => $orderId]);

            return response()->json([
                'status'  => 'success',
                'message' => 'Nagad Payment Gateway URL ready.',
                // নগদের এপিআই থেকে প্রাপ্ত রিডাইরেক্ট ইউআরএল
                'redirect_url' => config('services.nagad.base_url') . '/check-out/' . $orderId,
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
                    'status'         => 'paid',
                    'transaction_id' => $request->input('payment_ref_id'),
                ]);

                Log::channel('audit')->info('Nagad Payment Successful', [
                    'order_id' => $order->id,
                    'trx_id'   => $request->input('payment_ref_id'),
                ]);

                return redirect()->route('payment.success')->with('message', 'নগদ পেমেন্ট সফল হয়েছে!');
            }
        }

        return redirect()->route('payment.fail')->with('error', 'নগদ পেমেন্ট ব্যর্থ হয়েছে।');
    }


    /* =========================================================================
     | ৩. সার্বজনীন রেসপন্স ভিউ
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