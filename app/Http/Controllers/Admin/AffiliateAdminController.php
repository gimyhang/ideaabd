<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Affiliate;
use App\Models\AffiliateReferral;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class AffiliateAdminController extends Controller
{
    public function index(): View
    {
        $affiliates = Affiliate::with('user')->withCount('referrals')->latest()->paginate(20);
        $recentReferrals = AffiliateReferral::with(['affiliate.user', 'order'])->latest()->take(20)->get();

        $totalAffiliatesCount = Affiliate::count();
        $totalCommissionPaid = (float) Affiliate::sum('total_paid');
        $totalPendingBalance = (float) Affiliate::sum('balance');

        return view('admin.affiliates.index', compact(
            'affiliates',
            'recentReferrals',
            'totalAffiliatesCount',
            'totalCommissionPaid',
            'totalPendingBalance'
        ));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'user_id'         => 'required|exists:users,id|unique:affiliates,user_id',
            'affiliate_code'  => 'required|string|max:30|unique:affiliates,affiliate_code',
            'commission_rate' => 'required|numeric|min:0.5|max:50',
            'payout_method'   => 'nullable|string',
            'payout_details'  => 'nullable|string',
        ]);

        Affiliate::create([
            'user_id'         => $validated['user_id'],
            'affiliate_code'  => strtoupper(Str::slug($validated['affiliate_code'])),
            'commission_rate' => $validated['commission_rate'],
            'balance'         => 0.00,
            'total_earned'    => 0.00,
            'total_paid'      => 0.00,
            'payout_method'   => $validated['payout_method'] ?? 'bkash',
            'payout_details'  => $validated['payout_details'] ?? null,
            'status'          => 'active',
        ]);

        return redirect()->route('admin.affiliates.index')->with('success', 'নতুন ইনফ্লুয়েন্সার / অ্যাফিলিয়েট পার্টনার সফলভাবে যুক্ত হয়েছে।');
    }

    public function recordPayout(Request $request, Affiliate $affiliate): RedirectResponse
    {
        $validated = $request->validate([
            'amount' => 'required|numeric|min:1',
        ]);

        $amount = (float)$validated['amount'];

        if ($amount > (float)$affiliate->balance) {
            return redirect()->back()->with('error', 'পে-আউট ব্যালেন্সের চেয়ে বেশি হতে পারে না।');
        }

        $affiliate->decrement('balance', $amount);
        $affiliate->increment('total_paid', $amount);

        return redirect()->route('admin.affiliates.index')->with('success', "৳{$amount} টাকার কমিশন সফলভাবে পরিশোধিত হিসেবে মার্ক করা হয়েছে।");
    }
}
