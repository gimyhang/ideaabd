<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Schema;
use Modules\Review\Models\Review;

class ReviewController extends Controller
{
    /**
     * Ensure required review table columns exist (Self-healing schema)
     */
    protected function ensureSchemaExists(): void
    {
        if (!Schema::hasTable('reviews')) {
            Schema::create('reviews', function ($table) {
                $table->id();
                $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
                $table->foreignId('book_id')->nullable()->constrained('books')->cascadeOnDelete();
                $table->foreignId('ebook_id')->nullable()->constrained('ebooks')->cascadeOnDelete();
                $table->unsignedBigInteger('blog_post_id')->nullable()->index();
                $table->string('reviewer_name')->nullable();
                $table->string('reviewer_email')->nullable();
                $table->string('reviewer_phone')->nullable();
                $table->unsignedTinyInteger('rating')->default(5);
                $table->string('title')->nullable();
                $table->text('comment')->nullable();
                $table->boolean('is_approved')->default(true);
                $table->timestamps();
                $table->softDeletes();
            });
        } else {
            Schema::table('reviews', function ($table) {
                if (!Schema::hasColumn('reviews', 'blog_post_id')) {
                    $table->unsignedBigInteger('blog_post_id')->nullable()->after('ebook_id')->index();
                }
                if (!Schema::hasColumn('reviews', 'reviewer_name')) {
                    $table->string('reviewer_name')->nullable()->after('blog_post_id');
                }
                if (!Schema::hasColumn('reviews', 'reviewer_email')) {
                    $table->string('reviewer_email')->nullable()->after('reviewer_name');
                }
                if (!Schema::hasColumn('reviews', 'reviewer_phone')) {
                    $table->string('reviewer_phone')->nullable()->after('reviewer_email');
                }
            });
        }
    }

    /**
     * Submit a review/comment for a Book, Ebook, or Blog Post.
     * Supports both authenticated users and guests (with anti-bot security).
     */
    public function store(Request $request): JsonResponse|RedirectResponse
    {
        $this->ensureSchemaExists();

        // 1. Anti-Bot Honeypot Security Check
        if ($request->filled('review_hp_field') || $request->filled('website_trap')) {
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json(['success' => false, 'message' => 'রোবট ট্র্যাপ সনাক্ত হয়েছে!'], 422);
            }
            return back()->with('error', 'রোবট ট্র্যাপ সনাক্ত হয়েছে!');
        }

        // 2. Rate limiting (max 10 reviews/comments per IP per 10 minutes)
        $throttleKey = 'review_submit:' . $request->ip();
        if (RateLimiter::tooManyAttempts($throttleKey, 10)) {
            $seconds = RateLimiter::availableIn($throttleKey);
            $msg = "আপনি খুব দ্রুত মন্তব্য পাঠাচ্ছেন। অনুগ্রহ করে {$seconds} সেকেন্ড পর আবার চেষ্টা করুন।";
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json(['success' => false, 'message' => $msg], 429);
            }
            return back()->with('error', $msg);
        }

        // 3. Validation
        $rules = [
            'rating'       => 'nullable|integer|min:1|max:5',
            'comment'      => 'required|string|min:3|max:2000',
            'book_id'      => 'nullable|integer',
            'ebook_id'     => 'nullable|integer',
            'blog_post_id' => 'nullable|integer',
        ];

        if (!Auth::check()) {
            $rules['reviewer_name']  = 'required|string|max:100';
            $rules['reviewer_email'] = 'nullable|email|max:150';
            $rules['reviewer_phone'] = 'nullable|string|max:30';
        }

        $validated = $request->validate($rules, [
            'comment.required'       => 'আপনার সৎ মতামত বা মন্তব্য লিখুন।',
            'comment.min'            => 'মন্তব্যটি অত্যন্ত সংক্ষিপ্ত। অন্তত কয়েকটি শব্দ লিখুন।',
            'reviewer_name.required' => 'আপনার নাম প্রদান করুন।',
        ]);

        RateLimiter::hit($throttleKey, 600);

        // Sanitize comment
        $cleanComment = strip_tags(trim($validated['comment']));
        $rating = (int) ($validated['rating'] ?? 5);
        if ($rating < 1 || $rating > 5) {
            $rating = 5;
        }

        $userId = Auth::id();
        $reviewerName = Auth::check() ? Auth::user()->name : trim($validated['reviewer_name'] ?? 'পাঠক');
        $reviewerEmail = Auth::check() ? Auth::user()->email : trim($validated['reviewer_email'] ?? '');
        $reviewerPhone = Auth::check() ? Auth::user()->phone : trim($validated['reviewer_phone'] ?? '');

        $review = Review::create([
            'user_id'        => $userId,
            'book_id'        => $validated['book_id'] ?? null,
            'ebook_id'       => $validated['ebook_id'] ?? null,
            'blog_post_id'   => $validated['blog_post_id'] ?? null,
            'reviewer_name'  => $reviewerName,
            'reviewer_email' => $reviewerEmail,
            'reviewer_phone' => $reviewerPhone,
            'rating'         => $rating,
            'comment'        => $cleanComment,
            'is_approved'    => true, // Auto-approved for frictionless user engagement
        ]);

        $responsePayload = [
            'success'   => true,
            'message'   => 'আপনার মূল্যবান রিভিউ/মন্তব্যটি সফলভাবে জমা হয়েছে! ধন্যবাদ।',
            'review'    => [
                'id'            => $review->id,
                'reviewer_name' => $reviewerName,
                'rating'        => $rating,
                'comment'       => $cleanComment,
                'created_at'    => 'এখনই',
                'avatar_initial'=> mb_substr($reviewerName, 0, 1),
            ],
        ];

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json($responsePayload);
        }

        return back()->with('success', $responsePayload['message']);
    }

    /**
     * Get review list for a specific entity
     */
    public function list(Request $request): JsonResponse
    {
        $this->ensureSchemaExists();

        $query = Review::where('is_approved', true)->with('user');

        if ($request->filled('book_id')) {
            $query->where('book_id', $request->integer('book_id'));
        } elseif ($request->filled('ebook_id')) {
            $query->where('ebook_id', $request->integer('ebook_id'));
        } elseif ($request->filled('blog_post_id')) {
            $query->where('blog_post_id', $request->integer('blog_post_id'));
        }

        $reviews = $query->latest('id')->paginate(15);

        return response()->json([
            'success' => true,
            'reviews' => $reviews,
        ]);
    }
}
