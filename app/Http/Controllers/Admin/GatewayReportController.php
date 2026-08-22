<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuthorPayoutRequest;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class GatewayReportController extends Controller
{
    /**
     * Display Customer Payment Gateway Transaction Logs & Breakdown.
     */
    public function index(Request $request): View
    {
        $gateway  = $request->input('gateway');
        $status   = $request->input('status');
        $fromDate = $request->input('from_date');
        $toDate   = $request->input('to_date');
        $search   = $request->input('search');

        $query = Order::query()->latest('id');

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('order_number', 'like', "%{$search}%")
                  ->orWhere('transaction_id', 'like', "%{$search}%")
                  ->orWhere('customer_phone', 'like', "%{$search}%")
                  ->orWhere('customer_name', 'like', "%{$search}%");
            });
        }

        if ($gateway) {
            if ($gateway === 'bkash') {
                $query->where(fn($q) => $q->where('payment_method', 'like', '%bkash%')->orWhere('payment_method', 'like', '%বিকাশ%'));
            } elseif ($gateway === 'nagad') {
                $query->where(fn($q) => $q->where('payment_method', 'like', '%nagad%')->orWhere('payment_method', 'like', '%নগদ%'));
            } elseif ($gateway === 'sslcommerz' || $gateway === 'card') {
                $query->where(fn($q) => $q->whereIn('payment_method', ['card', 'sslcommerz', 'bank', 'visa', 'mastercard'])->orWhere('payment_method', 'like', '%card%'));
            } elseif ($gateway === 'cod') {
                $query->where(fn($q) => $q->whereIn('payment_method', ['cod', 'cash', 'cash_on_delivery'])->orWhere('payment_method', 'like', '%cash%'));
            } else {
                $query->where('payment_method', $gateway);
            }
        }

        if ($status) {
            if ($status === 'paid' || $status === 'success') {
                $query->where(fn($q) => $q->where('payment_status', 'paid')->orWhere('status', 'paid'));
            } elseif ($status === 'pending') {
                $query->where(fn($q) => $q->where('payment_status', 'unpaid')->orWhere('status', 'pending'));
            } elseif ($status === 'failed') {
                $query->where(fn($q) => $q->where('payment_status', 'failed')->orWhere('status', 'cancelled'));
            } else {
                $query->where('status', $status);
            }
        }

        if ($fromDate) {
            $query->whereDate('created_at', '>=', $fromDate);
        }
        if ($toDate) {
            $query->whereDate('created_at', '<=', $toDate);
        }

        $orders = (clone $query)->paginate(25)->withQueryString();

        // ─── GATEWAY SUMMARY CARDS ───
        $allOrdersQuery = Order::query();
        if ($fromDate) $allOrdersQuery->whereDate('created_at', '>=', $fromDate);
        if ($toDate) $allOrdersQuery->whereDate('created_at', '<=', $toDate);

        $bkashTotal = (clone $allOrdersQuery)
            ->where(fn($q) => $q->where('payment_method', 'like', '%bkash%')->orWhere('payment_method', 'like', '%বিকাশ%'))
            ->where(fn($q) => $q->where('payment_status', 'paid')->orWhere('status', 'paid')->orWhere('status', 'processing')->orWhere('status', 'completed'))
            ->sum('total_amount');

        $nagadTotal = (clone $allOrdersQuery)
            ->where(fn($q) => $q->where('payment_method', 'like', '%nagad%')->orWhere('payment_method', 'like', '%নগদ%'))
            ->where(fn($q) => $q->where('payment_status', 'paid')->orWhere('status', 'paid')->orWhere('status', 'processing')->orWhere('status', 'completed'))
            ->sum('total_amount');

        $sslTotal = (clone $allOrdersQuery)
            ->where(fn($q) => $q->whereIn('payment_method', ['card', 'sslcommerz', 'bank', 'visa', 'mastercard'])->orWhere('payment_method', 'like', '%card%'))
            ->where(fn($q) => $q->where('payment_status', 'paid')->orWhere('status', 'paid')->orWhere('status', 'processing')->orWhere('status', 'completed'))
            ->sum('total_amount');

        $codTotal = (clone $allOrdersQuery)
            ->where(fn($q) => $q->whereIn('payment_method', ['cod', 'cash', 'cash_on_delivery'])->orWhere('payment_method', 'like', '%cash%'))
            ->sum('total_amount');

        $totalTransactions = (clone $allOrdersQuery)->count();
        $totalVolume = (clone $allOrdersQuery)->sum('total_amount');

        return view('admin.gateways.index', compact(
            'orders',
            'bkashTotal',
            'nagadTotal',
            'sslTotal',
            'codTotal',
            'totalTransactions',
            'totalVolume'
        ));
    }

    /**
     * Display Royalty Payout Gateway Logs & Author Disbursal Transactions.
     */
    public function royaltyPayoutLogs(Request $request): View
    {
        $channel  = $request->input('channel');
        $status   = $request->input('status');
        $fromDate = $request->input('from_date');
        $toDate   = $request->input('to_date');
        $search   = $request->input('search');

        $query = AuthorPayoutRequest::with(['author', 'user', 'processor'])->latest('id');

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('transaction_ref', 'like', "%{$search}%")
                  ->orWhere('account_details', 'like', "%{$search}%")
                  ->orWhereHas('author', fn($a) => $a->where('name', 'like', "%{$search}%")->orWhere('phone', 'like', "%{$search}%"))
                  ->orWhereHas('user', fn($u) => $u->where('name', 'like', "%{$search}%")->orWhere('phone', 'like', "%{$search}%"));
            });
        }

        if ($channel) {
            $query->where('gateway_channel', $channel);
        }

        if ($status) {
            $query->where('status', $status);
        }

        if ($fromDate) {
            $query->whereDate('created_at', '>=', $fromDate);
        }
        if ($toDate) {
            $query->whereDate('created_at', '<=', $toDate);
        }

        $payoutLogs = (clone $query)->paginate(25)->withQueryString();

        // Gateway Disbursal Aggregations
        $stats = [
            'total_disbursed' => AuthorPayoutRequest::where('status', 'paid')->sum('net_payable_amount'),
            'bkash_payout'    => AuthorPayoutRequest::where('status', 'paid')->where(fn($q) => $q->where('payment_method', 'bkash')->orWhere('gateway_channel', 'bkash_api'))->sum('net_payable_amount'),
            'nagad_payout'    => AuthorPayoutRequest::where('status', 'paid')->where(fn($q) => $q->where('payment_method', 'nagad')->orWhere('gateway_channel', 'nagad_api'))->sum('net_payable_amount'),
            'bank_payout'     => AuthorPayoutRequest::where('status', 'paid')->where(fn($q) => $q->where('payment_method', 'bank')->orWhere('gateway_channel', 'bank_api'))->sum('net_payable_amount'),
            'total_fees'      => AuthorPayoutRequest::where('status', 'paid')->sum('gateway_fee'),
            'total_tax'       => AuthorPayoutRequest::where('status', 'paid')->sum('tax_deduction_amount'),
        ];

        return view('admin.gateways.royalty_payout_logs', compact('payoutLogs', 'stats'));
    }
}
