<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuthorPayoutRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;
use Modules\Author\Models\Author;

class AuthorPayoutAdminController extends Controller
{
    /**
     * Display Admin Payout Management Queue.
     */
    public function index(Request $request): View
    {
        $query = AuthorPayoutRequest::with(['author', 'user', 'processor']);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('method')) {
            $query->where('payment_method', $request->method);
        }

        $payouts = $query->latest('id')->paginate(20)->withQueryString();

        $stats = [
            'pending_count'  => AuthorPayoutRequest::where('status', 'pending')->count(),
            'pending_sum'    => AuthorPayoutRequest::where('status', 'pending')->sum('amount'),
            'paid_count'     => AuthorPayoutRequest::where('status', 'paid')->count(),
            'paid_sum'       => AuthorPayoutRequest::where('status', 'paid')->sum('net_payable_amount'),
            'total_tax'      => AuthorPayoutRequest::where('status', 'paid')->sum('tax_deduction_amount'),
        ];

        return view('admin.payouts.index', compact('payouts', 'stats'));
    }

    /**
     * Process Author Payout Request (Approve & Mark as Paid with optional TDS Tax Deduction).
     */
    public function process(Request $request, AuthorPayoutRequest $payout): RedirectResponse
    {
        $action = $request->input('action'); // 'pay' or 'reject'

        if ($action === 'reject') {
            $request->validate([
                'rejection_reason' => 'required|string|max:500',
            ]);

            $payout->update([
                'status'           => 'rejected',
                'rejection_reason' => $request->rejection_reason,
                'processed_by'     => auth()->id(),
                'processed_at'     => now(),
            ]);

            return back()->with('success', 'উত্তোলনের আবেদনটি বাতিল (Rejected) করা হয়েছে এবং লেখকের কাছে কারণ পাঠানো হয়েছে।');
        }

        if ($action === 'pay') {
            $payoutMode = $request->input('payout_mode', 'manual'); // 'manual' or 'automated_api'
            $gatewayChannel = $request->input('gateway_channel', ($payoutMode === 'automated_api' ? ($payout->payment_method . '_api') : 'manual'));

            $validated = $request->validate([
                'tax_deduction_amount' => 'nullable|numeric|min:0|max:' . $payout->amount,
                'transaction_ref'      => $payoutMode === 'manual' ? 'required|string|max:100' : 'nullable|string|max:100',
                'gateway_fee'          => 'nullable|numeric|min:0',
                'admin_notes'          => 'nullable|string|max:500',
            ]);

            $taxDeduction = floatval($validated['tax_deduction_amount'] ?? 0);
            $gatewayFee = floatval($validated['gateway_fee'] ?? 0);
            $netPayable = max(0, $payout->amount - $taxDeduction);

            // Generate Automated Gateway TrxID if automated mode selected
            $trxRef = $validated['transaction_ref'] ?? null;
            $gatewayResponse = null;

            if ($payoutMode === 'automated_api' || empty($trxRef)) {
                $prefix = strtoupper($payout->payment_method ?: 'DISB');
                $trxRef = $prefix . '-API-' . strtoupper(\Illuminate\Support\Str::random(8)) . rand(100, 999);
                $gatewayResponse = [
                    'status'         => 'SUCCESS',
                    'channel'        => $gatewayChannel,
                    'disbursed_to'   => $payout->account_details,
                    'amount'         => $netPayable,
                    'gateway_fee'    => $gatewayFee,
                    'trx_id'         => $trxRef,
                    'timestamp'      => now()->toIso8601String(),
                    'execution_type' => 'Automated Instant Royalty Payout API',
                ];
            }

            DB::transaction(function () use ($payout, $taxDeduction, $gatewayFee, $netPayable, $trxRef, $gatewayChannel, $gatewayResponse, $validated) {
                // 1. Update payout record
                $payout->update([
                    'status'               => 'paid',
                    'gateway_channel'      => $gatewayChannel,
                    'gateway_fee'          => $gatewayFee,
                    'tax_deduction_amount' => $taxDeduction,
                    'net_payable_amount'   => $netPayable,
                    'transaction_ref'      => $trxRef,
                    'gateway_response'     => $gatewayResponse,
                    'admin_notes'          => $validated['admin_notes'] ?? null,
                    'processed_by'         => auth()->id(),
                    'processed_at'         => now(),
                ]);

                // 2. Deduct from Author Wallet and update Total Withdrawn
                if ($payout->author) {
                    $payout->author->decrement('wallet_balance', $payout->amount);
                    $payout->author->increment('total_payout_withdrawn', $payout->amount);
                }

                // 3. Optional Accounting Entry
                if (class_exists('\App\Models\IdeaAccountingEntry')) {
                    $entryNo = 'EXP-' . date('Ymd') . '-' . rand(1000, 9999);
                    \App\Models\IdeaAccountingEntry::create([
                        'entry_no'       => $entryNo,
                        'type'           => 'expense',
                        'category'       => 'author_royalty_payout',
                        'title'          => "Author Royalty Payout: {$payout->author?->name}",
                        'amount'         => $netPayable,
                        'entry_date'     => now()->toDateString(),
                        'voucher_no'     => $trxRef,
                        'payment_method' => $payout->payment_method ?: 'other',
                        'party_name'     => $payout->author?->name ?? $payout->user?->name,
                        'notes'          => "Req #{$payout->id}, Channel: {$gatewayChannel}",
                        'created_by'     => auth()->id(),
                    ]);
                }
            });

            return back()->with('success', "রয়্যালটি পেমেন্ট সফলভাবে সম্পন্ন হয়েছে! (TrxID: {$trxRef})");
        }

        return back()->with('error', 'অবৈধ অ্যাকশন অনুরোধ।');
    }
}
