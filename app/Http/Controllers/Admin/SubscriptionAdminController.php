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
    public function index(Request $request): View
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

        $plans = SubscriptionPlan::withCount('subscriptions')->orderBy('price_bdt')->get();

        // Subscribers query with dynamic search & filtering
        $query = UserSubscription::with(['user', 'plan'])->latest();

        if ($request->filled('search')) {
            $term = trim($request->search);
            $query->whereHas('user', function ($q) use ($term) {
                $q->where('name', 'like', "%{$term}%")
                  ->orWhere('email', 'like', "%{$term}%")
                  ->orWhere('phone', 'like', "%{$term}%");
            })->orWhere('transaction_id', 'like', "%{$term}%");
        }

        if ($request->filled('plan_id')) {
            $query->where('plan_id', $request->plan_id);
        }

        if ($request->filled('status')) {
            if ($request->status === 'active') {
                $query->where('status', 'active')->where('expires_at', '>=', now());
            } elseif ($request->status === 'expired') {
                $query->where(function ($q) {
                    $q->where('status', 'expired')->orWhere('expires_at', '<', now());
                });
            } elseif ($request->status === 'cancelled') {
                $query->where('status', 'cancelled');
            }
        }

        $subscribers = $query->paginate(20)->withQueryString();

        // Users for grant modal
        $usersList = User::select('id', 'name', 'email', 'phone')->latest()->limit(50)->get();

        // Stats
        $activeSubscribersCount = UserSubscription::where('status', 'active')->where('expires_at', '>=', now())->count();
        $totalSubscriptionRevenue = (float) UserSubscription::where('status', 'active')->sum('amount_paid');
        $totalPagesReadThisMonth = (int) EbookReadingLog::where('read_date', '>=', now()->startOfMonth())->sum('pages_read');
        $activePlansCount = SubscriptionPlan::where('is_active', true)->count();

        return view('admin.subscriptions.index', compact(
            'plans',
            'subscribers',
            'usersList',
            'activeSubscribersCount',
            'totalSubscriptionRevenue',
            'totalPagesReadThisMonth',
            'activePlansCount'
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
            'is_featured'          => 'nullable|boolean',
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
            'is_featured'          => $request->boolean('is_featured', false),
            'is_active'            => $request->boolean('is_active', true),
        ]);

        return redirect()->route('admin.subscriptions.index')->with('success', 'নতুন সাবস্ক্রিপশন প্ল্যান সফলভাবে তৈরি করা হয়েছে।');
    }

    public function updatePlan(Request $request, SubscriptionPlan $plan): RedirectResponse
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
            'is_featured'          => 'nullable|boolean',
            'is_active'            => 'nullable|boolean',
        ]);

        $plan->update([
            'name'                 => $validated['name'],
            'description'          => $validated['description'] ?? null,
            'price_bdt'            => $validated['price_bdt'],
            'price_usd'            => $validated['price_usd'],
            'duration_days'        => $validated['duration_days'],
            'max_devices'          => $validated['max_devices'],
            'unlimited_ebooks'     => $request->boolean('unlimited_ebooks', true),
            'unlimited_audiobooks' => $request->boolean('unlimited_audiobooks', false),
            'unlimited_webzines'   => $request->boolean('unlimited_webzines', true),
            'is_featured'          => $request->boolean('is_featured', false),
            'is_active'            => $request->boolean('is_active', true),
        ]);

        return redirect()->route('admin.subscriptions.index')->with('success', 'প্ল্যানটির তথ্য সফলভাবে আপডেট করা হয়েছে।');
    }

    public function togglePlan(SubscriptionPlan $plan): RedirectResponse
    {
        $plan->update(['is_active' => !$plan->is_active]);
        $status = $plan->is_active ? 'সক্রিয়' : 'নিষ্ক্রিয়';
        return redirect()->route('admin.subscriptions.index')->with('success', "প্ল্যানটি সফলভাবে {$status} করা হয়েছে।");
    }

    public function destroyPlan(SubscriptionPlan $plan): RedirectResponse
    {
        if ($plan->subscriptions()->where('status', 'active')->exists()) {
            return redirect()->route('admin.subscriptions.index')->with('error', 'এই প্ল্যানের অধীনে সক্রিয় সাবস্ক্রাইবার রয়েছে, তাই প্ল্যানটি মুছে ফেলা সম্ভব নয়।');
        }

        $plan->delete();
        return redirect()->route('admin.subscriptions.index')->with('success', 'প্ল্যানটি সফলভাবে মুছে ফেলা হয়েছে।');
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

    public function cancelSubscription(UserSubscription $subscription): RedirectResponse
    {
        $subscription->update([
            'status'     => 'cancelled',
            'expires_at' => now(),
        ]);

        return redirect()->route('admin.subscriptions.index')->with('success', 'সাবস্ক্রিপশনটি বাতিল করা হয়েছে।');
    }
}

