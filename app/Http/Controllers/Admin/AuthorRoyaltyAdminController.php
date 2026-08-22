<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuthorPayoutRequest;
use App\Models\AuthorRoyalty;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Modules\Author\Models\Author;
use Modules\Ebook\Models\Ebook;

class AuthorRoyaltyAdminController extends Controller
{
    /**
     * Display E-Book Sales Report (Date-wise, Author-wise, Book-wise & 50% Share Split).
     */
    public function salesReport(Request $request): View
    {
        $fromDate = $request->input('from_date');
        $toDate = $request->input('to_date');
        $authorId = $request->input('author_id');
        $ebookId = $request->input('ebook_id');

        $query = AuthorRoyalty::with(['author', 'ebook', 'order', 'user']);

        if ($fromDate) {
            $query->whereDate('created_at', '>=', $fromDate);
        }
        if ($toDate) {
            $query->whereDate('created_at', '<=', $toDate);
        }
        if ($authorId) {
            $query->where('author_id', $authorId);
        }
        if ($ebookId) {
            $query->where('ebook_id', $ebookId);
        }

        $sales = (clone $query)->latest('id')->paginate(25)->withQueryString();

        // Summary Aggregations
        $summaryQuery = clone $query;
        $totalRevenue = (clone $summaryQuery)->sum('sale_price');
        $totalPlatformFee = (clone $summaryQuery)->sum('platform_fee');
        $totalAuthorRoyalty = (clone $summaryQuery)->sum('royalty_amount');
        $totalCopies = (clone $summaryQuery)->count();

        $authors = Author::whereNull('deleted_at')->orderBy('name')->get(['id', 'name']);
        $ebooks = Ebook::whereNull('deleted_at')->orderBy('title')->get(['id', 'title']);

        return view('admin.royalties.sales_report', compact(
            'sales',
            'totalRevenue',
            'totalPlatformFee',
            'totalAuthorRoyalty',
            'totalCopies',
            'authors',
            'ebooks'
        ));
    }

    /**
     * Display Royalty Management / Ledger & Author Balances.
     */
    public function index(Request $request): View
    {
        $search = $request->input('search');

        // Query Authors with dynamic royalty aggregations
        $authorsQuery = Author::whereNull('deleted_at')
            ->withCount(['royalties as total_sales_count' => function ($q) {
                $q->where('status', '!=', 'refunded');
            }])
            ->withSum(['royalties as total_earned_sum' => function ($q) {
                $q->where('status', '!=', 'refunded');
            }], 'royalty_amount')
            ->withSum(['payoutRequests as total_paid_sum' => function ($q) {
                $q->where('status', 'paid');
            }], 'amount');

        if ($search) {
            $authorsQuery->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        $authors = $authorsQuery->orderByDesc('wallet_balance')->paginate(20)->withQueryString();

        // System Wide Aggregations
        $stats = [
            'total_earned'    => AuthorRoyalty::where('status', '!=', 'refunded')->sum('royalty_amount'),
            'total_paid'      => AuthorPayoutRequest::where('status', 'paid')->sum('amount'),
            'current_balance' => Author::sum('wallet_balance'),
            'pending_payouts' => AuthorPayoutRequest::where('status', 'pending')->sum('amount'),
        ];

        // Recent Royalty Ledger Entries
        $recentLedger = AuthorRoyalty::with(['author', 'ebook', 'order'])
            ->latest('id')
            ->take(10)
            ->get();

        $authorList = Author::whereNull('deleted_at')->orderBy('name')->get(['id', 'name']);

        return view('admin.royalties.index', compact('authors', 'stats', 'recentLedger', 'authorList'));
    }

    /**
     * Store Manual Royalty Adjustment (Credit or Debit log to Author Wallet).
     */
    public function storeAdjustment(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'author_id'       => 'required|exists:authors,id',
            'adjustment_type' => 'required|in:credit,debit',
            'amount'          => 'required|numeric|min:1',
            'ebook_id'        => 'nullable|exists:ebooks,id',
            'reason'          => 'required|string|max:500',
        ]);

        $author = Author::findOrFail($validated['author_id']);
        $amount = (float) $validated['amount'];
        $type = $validated['adjustment_type'];

        DB::transaction(function () use ($author, $amount, $type, $validated) {
            if ($type === 'credit') {
                $author->increment('wallet_balance', $amount);
                $royaltyAmount = $amount;
                $platformFee = 0.00;
            } else {
                $author->decrement('wallet_balance', min((float)$author->wallet_balance, $amount));
                $royaltyAmount = -$amount;
                $platformFee = 0.00;
            }

            // Record in AuthorRoyalty log
            AuthorRoyalty::create([
                'author_id'          => $author->id,
                'user_id'            => $author->user_id,
                'ebook_id'           => $validated['ebook_id'] ?? (Ebook::where('author_id', $author->id)->value('id') ?: Ebook::first()?->id),
                'sale_price'         => 0.00,
                'royalty_percentage' => 100.00,
                'royalty_amount'     => $royaltyAmount,
                'platform_fee'       => $platformFee,
                'status'             => $type === 'credit' ? 'manual_credit' : 'manual_debit',
            ]);
        });

        $msg = $type === 'credit'
            ? "লেখক ‘{$author->name}’-এর ওয়ালেটে সফলভাবে ৳" . number_format($amount, 2) . " ক্রেডিট (যোগ) করা হয়েছে।"
            : "লেখক ‘{$author->name}’-এর ওয়ালেট থেকে সফলভাবে ৳" . number_format($amount, 2) . " ডেবিট (কর্তন) করা হয়েছে।";

        return back()->with('success', $msg);
    }

    /**
     * Printable Payout Receipt / Voucher.
     */
    public function payoutReceipt(int $id): View
    {
        $payout = AuthorPayoutRequest::with(['author', 'user', 'processor'])->findOrFail($id);
        return view('admin.payouts.receipt', compact('payout'));
    }
}
