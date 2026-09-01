<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\EbookReadingLog;
use App\Models\SubscriptionPlan;
use App\Models\User;
use App\Models\UserSubscription;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class SubscriptionAdminController extends Controller
{
    public function index(): View
    {
        // Seed default plans if empty
        if (SubscriptionPlan::count() === 0) {
            SubscriptionPlan::create([
                'name'                 => 'আইডিয়া আনলিমিটেড মাসিক (Idea Unlimited Monthly)',
                'slug'                 => 'idea-unlimited-monthly',
                'description'          => 'সকল ই-বুক ও ডিজিটাল ম্যাগাজিন সীমাহীন পড়ার মেম্বারশিপ।',
                'price_bdt'            => 299.00,
                'price_usd'            => 3.99,
                'duration_days'        => 30,
                'max_devices'          => 3,
                'unlimited_ebooks'     => true,
                'unlimited_audiobooks' => true,
                'unlimited_webzines'   => true,
                'is_featured'          => true,
                'is_active'            => true,
            ]);

            SubscriptionPlan::create([
                'name'                 => 'আইডিয়া স্কলার বাৎসরিক (Annual Scholar Pass)',
                'slug'                 => 'idea-scholar-annual',
                'description'          => 'সম্পূর্ণ ১ বছর সকল ই-বুক, গবেষণাপত্র ও ম্যাগাজিনের সম্পূর্ণ এক্সেস।',
                'price_bdt'            => 2499.00,
                'price_usd'            => 24.99,
                'duration_days'        => 365,
                'max_devices'          => 5,
                'unlimited_ebooks'     => true,
                'unlimited_audiobooks' => true,
                'unlimited_webzines'   => true,
                'is_featured'          => false,
                'is_active'            => true,
            ]);
        }

        $plans = SubscriptionPlan::withCount('subscriptions')->get();
        $subscribers = UserSubscription::with(['user', 'plan'])->latest()->paginate(20);

        // Stats
        $activeSubscribersCount = UserSubscription::where('status', 'active')->where('expires_at', '>=', now())->count();
        $totalSubscriptionRevenue = (float) UserSubscription::where('status', 'active')->sum('amount_paid');
        $totalPagesReadThisMonth = (int) EbookReadingLog::where('read_date', '>=', now()->startOfMonth())->sum('pages_read');

        return view('admin.subscriptions.index', compact(
            'plans',
            'subscribers',
            'activeSubscribersCount',
            'totalSubscriptionRevenue',
            'totalPagesReadThisMonth'
        ));
    }

    public function storePlan(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name'                 => 'required|string|max:255',
            'price_bdt'            => 'required|numeric|min:0',
            'price_usd'            => 'required|numeric|min:0',
            'duration_days'        => 'required|integer|min:1',
            'max_devices'          => 'required|integer|min:1|max:10',
            'description'          => 'nullable|string',
            'unlimited_ebooks'     => 'nullable|boolean',
            'unlimited_audiobooks' => 'nullable|boolean',
            'unlimited_webzines'   => 'nullable|boolean',
            'is_active'            => 'nullable|boolean',
        ]);

        SubscriptionPlan::create([
            'name'                 => $validated['name'],
            'slug'                 => Str::slug($validated['name']) . '-' . rand(100, 999),
            'description'          => $validated['description'] ?? null,
            'price_bdt'            => $validated['price_bdt'],
            'price_usd'            => $validated['price_usd'],
            'duration_days'        => $validated['duration_days'],
            'max_devices'          => $validated['max_devices'],
            'unlimited_ebooks'     => $request->boolean('unlimited_ebooks', true),
            'unlimited_audiobooks' => $request->boolean('unlimited_audiobooks', false),
            'unlimited_webzines'   => $request->boolean('unlimited_webzines', true),
            'is_active'            => $request->boolean('is_active', true),
        ]);

        return redirect()->route('admin.subscriptions.index')->with('success', 'নতুন সাবস্ক্রিপশন প্ল্যান তৈরি করা হয়েছে।');
    }

    public function grantSubscription(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'user_id'        => 'required|exists:users,id',
            'plan_id'        => 'required|exists:subscription_plans,id',
            'payment_method' => 'nullable|string',
            'amount_paid'    => 'nullable|numeric|min:0',
        ]);

        $plan = SubscriptionPlan::findOrFail($validated['plan_id']);

        UserSubscription::create([
            'user_id'        => $validated['user_id'],
            'plan_id'        => $plan->id,
            'starts_at'      => now(),
            'expires_at'     => now()->addDays($plan->duration_days),
            'status'         => 'active',
            'payment_method' => $validated['payment_method'] ?? 'manual_admin',
            'amount_paid'    => $validated['amount_paid'] ?? $plan->price_bdt,
            'currency'       => 'BDT',
            'auto_renew'     => false,
        ]);

        return redirect()->route('admin.subscriptions.index')->with('success', 'ব্যবহারকারীকে সফলভাবে সাবস্ক্রিপশন প্রদান করা হয়েছে।');
    }
}
