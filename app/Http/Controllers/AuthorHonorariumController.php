<?php

namespace App\Http\Controllers;

use App\Models\AdminDashboardSetting;
use App\Models\AuthorHonorarium;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Modules\Author\Models\Author;
use Modules\Blog\Models\BlogPost;

class AuthorHonorariumController extends Controller
{
    /**
     * Store and process a reader's honorarium (সম্মানি) for a blog post author.
     */
    public function store(Request $request): JsonResponse|RedirectResponse
    {
        $validated = $request->validate([
            'blog_post_id'          => 'required|exists:blog_posts,id',
            'amount'                => 'required|numeric|min:5|max:100000',
            'payment_method'        => 'nullable|string|in:bkash,nagad,rocket,card,sslcommerz,manual',
            'sender_name'           => 'nullable|string|max:150',
            'sender_phone'          => 'nullable|string|max:50',
            'sender_email'          => 'nullable|email|max:150',
            'sender_account_number' => 'nullable|string|max:50',
            'trx_id'                => 'required|string|max:100',
            'message'               => 'nullable|string|max:1000',
            'is_anonymous'          => 'nullable|boolean',
        ], [
            'amount.required' => 'অনুগ্রহ করে সম্মানির টাকার পরিমাণ লিখুন বা নির্বাচন করুন।',
            'amount.min'      => 'সম্মানির পরিমাণ সর্বনিম্ন ৫ টাকা হতে হবে।',
            'amount.max'      => 'সম্মানির পরিমাণ সর্বোচ্চ ১,০০,০০০ টাকা হতে পারে।',
            'trx_id.required' => 'অনুগ্রহ করে পেমেন্টের ট্রানজেকশন আইডি (TrxID) প্রদান করুন।',
        ]);

        $post = BlogPost::findOrFail($validated['blog_post_id']);
        $author = $post->resolveAuthorRecord();
        $authorUserId = null;

        if ($author) {
            $authorUserId = $author->user_id;
        } elseif ($post->author_id) {
            $authorUserId = $post->author_id;
            $userObj = User::find($authorUserId);
            $author = $userObj?->getAuthorRecord();
        }

        // If author record still not created, auto-create one for this writer
        if (!$author) {
            $authorName = $post->author_name ?: 'সম্পাদকীয় লেখক';
            $author = Author::findOrCreateUnified([
                'name'               => $authorName,
                'email'              => $post->author?->email,
                'phone'              => $post->author?->phone ?: $post->owner_phone,
                'is_active'          => true,
                'is_verified'        => true,
                'royalty_percentage' => 50.00,
                'wallet_balance'     => 0.00,
            ]);
            if ($authorUserId && empty($author->user_id)) {
                $author->update(['user_id' => $authorUserId]);
            }
        }

        $amount = round((float) $validated['amount'], 2);
        // 70% goes to author, 30% goes to site maintenance fee
        $platformFee = round($amount * 0.30, 2); // ৩০% সাইট মেইনটেনেন্স বিল
        $authorAmount = round($amount - $platformFee, 2); // ৭০% লেখক পাবেন

        $donorUserId = auth()->check() ? auth()->id() : null;
        $donorName = !empty($validated['sender_name']) ? trim($validated['sender_name']) : (auth()->check() ? auth()->user()->name : 'সম্মানিত পাঠক');
        $donorPhone = !empty($validated['sender_phone']) ? trim($validated['sender_phone']) : (auth()->check() ? auth()->user()->phone : null);
        $donorEmail = !empty($validated['sender_email']) ? trim($validated['sender_email']) : (auth()->check() ? auth()->user()->email : null);
        $isAnonymous = !empty($validated['is_anonymous']);
        $paymentMethod = $validated['payment_method'] ?? 'bkash';

        $trxId = strtoupper(trim($validated['trx_id']));
        $paymentStatus = 'completed'; // Direct appreciation honorarium is recorded as completed

        try {
            $honorarium = DB::transaction(function () use (
                $author,
                $authorUserId,
                $post,
                $donorUserId,
                $donorName,
                $donorPhone,
                $donorEmail,
                $validated,
                $amount,
                $platformFee,
                $authorAmount,
                $paymentMethod,
                $trxId,
                $paymentStatus,
                $isAnonymous
            ) {
                // 1. Create Honorarium record
                $record = AuthorHonorarium::create([
                    'author_id'             => $author?->id,
                    'author_user_id'        => $authorUserId ?: $author?->user_id,
                    'blog_post_id'          => $post->id,
                    'donor_user_id'         => $donorUserId,
                    'donor_name'            => $donorName,
                    'donor_phone'           => $donorPhone,
                    'donor_email'           => $donorEmail,
                    'message'               => $validated['message'] ?? null,
                    'amount'                => $amount,
                    'platform_fee'          => $platformFee,
                    'author_amount'         => $authorAmount,
                    'payment_method'        => $paymentMethod,
                    'payment_channel'       => 'reader_tip',
                    'sender_account_number' => $validated['sender_account_number'] ?? null,
                    'trx_id'                => $trxId,
                    'payment_status'        => $paymentStatus,
                    'is_anonymous'          => $isAnonymous,
                ]);

                // 2. Credit Author's Wallet (70% share)
                if ($author) {
                    $author->increment('wallet_balance', $authorAmount);
                }

                // 3. Optional Accounting Entry
                if (class_exists('\App\Models\IdeaAccountingEntry')) {
                    \App\Models\IdeaAccountingEntry::create([
                        'entry_no'       => 'INC-' . date('Ymd') . '-' . rand(1000, 9999),
                        'type'           => 'income',
                        'category'       => 'author_honorarium_reader_tip',
                        'title'          => "পাঠক সম্মানি: {$author?->name} (পোস্ট: {$post->title})",
                        'amount'         => $amount,
                        'entry_date'     => now()->toDateString(),
                        'voucher_no'     => $trxId,
                        'payment_method' => $paymentMethod,
                        'party_name'     => $donorName,
                        'notes'          => "সম্মানি মোট: ৳{$amount} (লেখক ৭০%: ৳{$authorAmount}, সাইট মেইনটেনেন্স ৩০%: ৳{$platformFee}) | TrxID: {$trxId}",
                        'created_by'     => auth()->id() ?? 1,
                    ]);
                }

                return $record;
            });

            Log::channel('audit')->info('Author Honorarium Recorded', [
                'honorarium_id' => $honorarium->id,
                'author_id'     => $author?->id,
                'post_id'       => $post->id,
                'amount'        => $amount,
                'author_amount' => $authorAmount,
                'platform_fee'  => $platformFee,
                'trx_id'        => $trxId,
            ]);

            $successMsg = 'আপনার ' . number_format($amount, 0) . ' ৳ সম্মানি ও ভালোবাসা সফলভাবে লেখকের কাছে পৌঁছেছে! লেখকের পক্ষ থেকে আপনাকে আন্তরিক শুভেচ্ছা ও ধন্যবাদ।';

            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'success'       => true,
                    'message'       => $successMsg,
                    'honorarium_id' => $honorarium->id,
                    'amount'        => $amount,
                    'author_amount' => $authorAmount,
                    'platform_fee'  => $platformFee,
                    'author_name'   => $author?->name ?: $post->author_name,
                ]);
            }

            return back()->with('honorarium_success', $successMsg);

        } catch (\Throwable $e) {
            Log::channel('json')->error('Honorarium Processing Error', [
                'post_id' => $post->id,
                'error'   => $e->getMessage(),
            ]);

            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'সম্মানি গ্রহণ প্রক্রিয়ায় একটি সমস্যা হয়েছে। অনুগ্রহ করে কিছুক্ষণ পর আবার চেষ্টা করুন।',
                ], 500);
            }

            return back()->with('error', 'সম্মানি গ্রহণ প্রক্রিয়ায় সমস্যা হয়েছে।');
        }
    }
}
