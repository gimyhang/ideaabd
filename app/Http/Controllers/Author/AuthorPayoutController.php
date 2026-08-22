<?php

namespace App\Http\Controllers\Author;

use App\Http\Controllers\Controller;
use App\Models\AuthorPayoutRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AuthorPayoutController extends Controller
{
    /**
     * Display Author Payout Requests & Balance.
     */
    public function index(): View
    {
        $user = auth()->user();
        $author = $user->getAuthorRecord();

        $walletBalance = (float) ($author?->wallet_balance ?? 0.00);
        $totalWithdrawn = (float) ($author?->total_payout_withdrawn ?? 0.00);

        $pendingAmount = AuthorPayoutRequest::where('user_id', $user->id)
            ->where('status', 'pending')
            ->sum('amount');

        $availableBalance = max(0, $walletBalance - $pendingAmount);

        $payoutRequests = AuthorPayoutRequest::where('user_id', $user->id)
            ->latest('id')
            ->paginate(15);

        return view('author.payouts', compact(
            'author',
            'walletBalance',
            'totalWithdrawn',
            'pendingAmount',
            'availableBalance',
            'payoutRequests'
        ));
    }

    /**
     * Store new Author Payout / Withdrawal Request.
     */
    public function storeRequest(Request $request): RedirectResponse
    {
        $user = auth()->user();
        $author = $user->getAuthorRecord();

        if (!$author) {
            return back()->with('error', 'আপনার লেখক প্রোফাইল পাওয়া যায়নি।');
        }

        $walletBalance = (float) $author->wallet_balance;
        $pendingAmount = (float) AuthorPayoutRequest::where('user_id', $user->id)
            ->where('status', 'pending')
            ->sum('amount');

        $availableBalance = max(0, $walletBalance - $pendingAmount);

        $validated = $request->validate([
            'amount'          => 'required|numeric|min:1000|max:' . $availableBalance,
            'payment_method'  => 'required|string|in:bkash,nagad,rocket,bank',
            'account_details' => 'required|string|max:500',
            'admin_notes'     => 'nullable|string|max:500',
        ], [
            'amount.min' => 'উত্তোলনের জন্য সর্বনিম্ন ব্যালেন্স ১,০০০ (এক হাজার) টাকা হতে হবে।',
            'amount.max' => 'আপনার উত্তোলনের জন্য পর্যাপ্ত ওয়ালেট ব্যালেন্স নেই। (সর্বোচ্চ প্রাপ্য: ৳' . number_format($availableBalance, 2) . ')',
        ]);

        AuthorPayoutRequest::create([
            'author_id'            => $author->id,
            'user_id'              => $user->id,
            'amount'               => $validated['amount'],
            'payment_method'       => $validated['payment_method'],
            'account_details'      => $validated['account_details'],
            'tax_deduction_amount' => 0.00,
            'net_payable_amount'   => $validated['amount'],
            'status'               => 'pending',
            'admin_notes'          => $validated['admin_notes'] ?? null,
        ]);

        // Save payout preference for next time
        $author->update([
            'payout_account_type'    => $validated['payment_method'],
            'payout_account_details' => $validated['account_details'],
        ]);

        return redirect()->route('author.payouts.index')
            ->with('success', 'আপনার রয়্যালটি উত্তোলনের আবেদনটি (Payout Request: ৳' . number_format($validated['amount'], 2) . ') সফলভাবে জমা হয়েছে! অ্যাডমিন পর্যালোচনা করে শীঘ্রই পেমেন্ট প্রদান করবেন।');
    }
}
