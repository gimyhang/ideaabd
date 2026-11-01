<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuthorHonorarium;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Modules\Author\Models\Author;
use Modules\Blog\Models\BlogPost;

class AuthorHonorariumAdminController extends Controller
{
    /**
     * Display Admin Honorarium Ledger & Analytics.
     */
    public function index(Request $request): View
    {
        $query = AuthorHonorarium::with(['author', 'authorUser', 'post', 'donor']);

        // Search filter
        if ($request->filled('search')) {
            $search = trim($request->string('search'));
            $query->where(function ($q) use ($search) {
                $q->where('donor_name', 'like', "%{$search}%")
                  ->orWhere('donor_phone', 'like', "%{$search}%")
                  ->orWhere('donor_email', 'like', "%{$search}%")
                  ->orWhere('trx_id', 'like', "%{$search}%")
                  ->orWhere('sender_account_number', 'like', "%{$search}%")
                  ->orWhereHas('author', function ($aq) use ($search) {
                      $aq->where('name', 'like', "%{$search}%");
                  })
                  ->orWhereHas('post', function ($pq) use ($search) {
                      $pq->where('title', 'like', "%{$search}%");
                  });
            });
        }

        // Author filter
        if ($request->filled('author_id')) {
            $query->where('author_id', $request->author_id);
        }

        // Payment Method filter
        if ($request->filled('method')) {
            $query->where('payment_method', $request->method);
        }

        // Status filter
        if ($request->filled('status')) {
            $query->where('payment_status', $request->status);
        }

        // Date Range
        if ($request->filled('from_date')) {
            $query->whereDate('created_at', '>=', $request->from_date);
        }
        if ($request->filled('to_date')) {
            $query->whereDate('created_at', '<=', $request->to_date);
        }

        $honorariums = (clone $query)->latest('id')->paginate(20)->withQueryString();

        // System Wide Statistics
        $totalCollected = (float) AuthorHonorarium::where('payment_status', 'completed')->sum('amount');
        $totalAuthorEarned = (float) AuthorHonorarium::where('payment_status', 'completed')->sum('author_amount');
        $totalPlatformFee = (float) AuthorHonorarium::where('payment_status', 'completed')->sum('platform_fee');
        $totalCount = AuthorHonorarium::where('payment_status', 'completed')->count();
        $thisMonthSum = (float) AuthorHonorarium::where('payment_status', 'completed')
            ->whereYear('created_at', now()->year)
            ->whereMonth('created_at', now()->month)
            ->sum('amount');
        $thisMonthPlatformFee = (float) AuthorHonorarium::where('payment_status', 'completed')
            ->whereYear('created_at', now()->year)
            ->whereMonth('created_at', now()->month)
            ->sum('platform_fee');
        $pendingCount = AuthorHonorarium::where('payment_status', 'pending')->count();

        $authors = Author::whereNull('deleted_at')->orderBy('name')->get(['id', 'name']);

        return view('admin.honorariums.index', compact(
            'honorariums',
            'totalCollected',
            'totalAuthorEarned',
            'totalPlatformFee',
            'totalCount',
            'thisMonthSum',
            'thisMonthPlatformFee',
            'pendingCount',
            'authors'
        ));
    }

    /**
     * Update status of an honorarium record.
     */
    public function updateStatus(Request $request, $id): RedirectResponse
    {
        $validated = $request->validate([
            'status'      => 'required|in:completed,pending,rejected,refunded',
            'admin_notes' => 'nullable|string|max:500',
        ]);

        $honorarium = AuthorHonorarium::findOrFail($id);
        $oldStatus = $honorarium->payment_status;
        $newStatus = $validated['status'];

        DB::transaction(function () use ($honorarium, $oldStatus, $newStatus, $validated) {
            $honorarium->update([
                'payment_status' => $newStatus,
                'admin_notes'    => $validated['admin_notes'] ?? $honorarium->admin_notes,
            ]);

            // Adjust author wallet balance if status shifted
            if ($honorarium->author) {
                if ($oldStatus === 'completed' && in_array($newStatus, ['rejected', 'refunded'], true)) {
                    $honorarium->author->decrement('wallet_balance', $honorarium->author_amount);
                } elseif ($oldStatus !== 'completed' && $newStatus === 'completed') {
                    if ($honorarium->author_amount > 0) {
                        $honorarium->author->increment('wallet_balance', $honorarium->author_amount);
                    }
                    if (class_exists('\App\Models\IdeaAccountingEntry')) {
                        $exists = \App\Models\IdeaAccountingEntry::where('category', 'author_honorarium_reader_tip')
                            ->where('voucher_no', $honorarium->trx_id)
                            ->exists();
                        if (!$exists) {
                            \App\Models\IdeaAccountingEntry::create([
                                'entry_no'       => 'INC-' . date('Ymd') . '-' . rand(1000, 9999),
                                'type'           => 'income',
                                'category'       => 'author_honorarium_reader_tip',
                                'title'          => "পাঠক সম্মানি: {$honorarium->author?->name} (পোস্ট: {$honorarium->post?->title})",
                                'amount'         => $honorarium->amount,
                                'entry_date'     => now()->toDateString(),
                                'voucher_no'     => $honorarium->trx_id,
                                'payment_method' => $honorarium->payment_method ?: 'bkash',
                                'party_name'     => $honorarium->donor_name ?: 'সম্মানিত পাঠক',
                                'notes'          => "সম্মানি মোট: ৳{$honorarium->amount} (লেখক: ৳{$honorarium->author_amount}, প্ল্যাটফর্ম ফি: ৳{$honorarium->platform_fee}) | TrxID: {$honorarium->trx_id}",
                                'created_by'     => auth()->id() ?? 1,
                            ]);
                        }
                    }
                }
            }
        });

        return back()->with('success', "সম্মানি রেকর্ডের স্ট্যাটাস পরিবর্তিত হয়ে '{$newStatus}' হয়েছে।");
    }

    /**
     * Delete an honorarium record.
     */
    public function destroy($id): RedirectResponse
    {
        $honorarium = AuthorHonorarium::findOrFail($id);

        if ($honorarium->payment_status === 'completed' && $honorarium->author) {
            $honorarium->author->decrement('wallet_balance', $honorarium->author_amount);
        }

        $honorarium->delete();

        return back()->with('success', 'সম্মানি রেকর্ড সফলভাবে মুছে ফেলা হয়েছে।');
    }
}
