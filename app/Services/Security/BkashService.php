<?php

namespace App\Services\Security;

use Exception;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class BkashService
{
    private string $baseUrl;
    private string $appKey;
    private string $appSecret;
    private string $username;
    private string $password;

    public function __construct()
    {
        $this->baseUrl   = config('services.bkash.base_url');
        $this->appKey    = config('services.bkash.app_key');
        $this->appSecret = config('services.bkash.app_secret');
        $this->username  = config('services.bkash.username');
        $this->password  = config('services.bkash.password');
    }

    /**
     * বিকাশের Authorization Token তৈরি অথবা ক্যাশ থেকে নেওয়া (৫৮ মিনিট)
     */
    public function getToken(): string
    {
        return Cache::remember('bkash_id_token', 3500, function () {
            $response = Http::timeout(30)
                ->withHeaders([
                    'username'     => $this->username,
                    'password'     => $this->password,
                    'Content-Type' => 'application/json',
                ])
                ->post("{$this->baseUrl}/checkout/token/grant", [
                    'app_key'    => $this->appKey,
                    'app_secret' => $this->appSecret,
                ]);

            if ($response->successful() && isset($response->json()['id_token'])) {
                return $response->json()['id_token'];
            }

            Log::error('bKash Token Generation Failed', [
                'status'   => $response->status(),
                'response' => $response->json(),
            ]);

            throw new Exception('bKash authentication failed.');
        });
    }

    /**
     * পেমেন্ট ইনভয়েস/রিকোয়েস্ট তৈরি করা
     */
    public function createPayment(string $invoiceNumber, float $amount, string $phone): array
    {
        $token = $this->getToken();

        $response = Http::timeout(30)
            ->withHeaders([
                'Authorization' => $token,
                'X-APP-Key'     => $this->appKey,
                'Content-Type'  => 'application/json',
            ])
            ->post("{$this->baseUrl}/checkout/create", [
                'mode'                  => '0011',
                'payerReference'        => $phone,
                'callbackURL'           => config('services.bkash.callback_url') ?? (Route::has('bkash.callback') ? route('bkash.callback') : route('api.payment.bkash.callback')),
                'amount'                => number_format($amount, 2, '.', ''),
                'currency'              => 'BDT',
                'intent'                => 'sale',
                'merchantInvoiceNumber' => $invoiceNumber,
            ]);

        if ($response->failed()) {
            Log::error('bKash Create Payment Failed', [
                'invoice'  => $invoiceNumber,
                'response' => $response->json(),
            ]);
            throw new Exception('bKash payment request creation failed.');
        }

        return $response->json();
    }

    /**
     * পেমেন্ট এক্সিকিউট করা
     */
    public function executePayment(string $paymentID): array
    {
        $token = $this->getToken();

        $response = Http::timeout(30)
            ->withHeaders([
                'Authorization' => $token,
                'X-APP-Key'     => $this->appKey,
                'Content-Type'  => 'application/json',
            ])
            ->post("{$this->baseUrl}/checkout/execute", [
                'paymentID' => $paymentID,
            ]);

        if ($response->failed()) {
            Log::error('bKash Execute Payment Failed', [
                'paymentID' => $paymentID,
                'response'  => $response->json(),
            ]);
            throw new Exception('bKash payment execution failed.');
        }

        return $response->json();
    }

    /**
     * পেমেন্ট স্ট্যাটাস রি-চেক করা (Reconciliation)
     */
    public function queryPayment(string $paymentID): array
    {
        $token = $this->getToken();

        $response = Http::timeout(30)
            ->withHeaders([
                'Authorization' => $token,
                'X-APP-Key'     => $this->appKey,
                'Content-Type'  => 'application/json',
            ])
            ->post("{$this->baseUrl}/checkout/payment/status", [
                'paymentID' => $paymentID,
            ]);

        return $response->json() ?? [];
    }
}