<?php

namespace App\Http\Controllers\Author;

use App\Http\Controllers\Controller;
use App\Models\AuthorHonorarium;
use App\Models\AuthorPayoutRequest;
use App\Models\AuthorRoyalty;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Modules\Blog\Models\BlogPost;
use Modules\Ebook\Models\Ebook;

class AuthorDashboardController extends Controller
{
    /**
     * Display Author KDP Portal Dashboard.
     */
    public function dashboard(): View
    {
        $user = auth()->user();
        $author = $user->getAuthorRecord();

        // 1. Author's Ebooks stats
        $ebooksQuery = Ebook::where(function ($q) use ($user, $author) {
            $q->where('author_user_id', $user->id);
            if ($author) {
                $q->orWhere('author_id', $author->id);
            }
        });

        $totalEbooks = (clone $ebooksQuery)->count();
        $publishedEbooks = (clone $ebooksQuery)->where('mod_status', 'approved')->where('is_active', true)->count();
        $pendingEbooks = (clone $ebooksQuery)->where('mod_status', 'pending')->count();
        $rejectedEbooks = (clone $ebooksQuery)->where('mod_status', 'rejected')->count();

        // 2. Royalty Stats (E-books)
        $royaltiesQuery = AuthorRoyalty::where(function ($q) use ($user, $author) {
            $q->where('user_id', $user->id);
            if ($author) {
                $q->orWhere('author_id', $author->id);
            }
        });

        $totalRoyaltyEarned = (clone $royaltiesQuery)->where('status', '!=', 'refunded')->sum('royalty_amount');
        $totalCopiesSold = (clone $royaltiesQuery)->where('status', '!=', 'refunded')->count();

        // 3. IdeaPatra Reader Honorariums (পড়ে ভালো লাগা সম্মানি)
        $hasHonorariumTable = \Illuminate\Support\Facades\Schema::hasTable('author_honorariums');
        $totalHonorariumEarned = 0.00;
        $totalHonorariumCount = 0;
        $recentHonorariums = collect();

        if ($hasHonorariumTable) {
            $honorariumsQuery = AuthorHonorarium::where(function ($q) use ($user, $author) {
                $q->where('author_user_id', $user->id);
                if ($author) {
                    $q->orWhere('author_id', $author->id);
                }
            });

            $totalHonorariumEarned = (clone $honorariumsQuery)->where('payment_status', 'completed')->sum('author_amount');
            $totalHonorariumCount = (clone $honorariumsQuery)->where('payment_status', 'completed')->count();
            $recentHonorariums = (clone $honorariumsQuery)->with('post')->latest('id')->take(6)->get();
        }

        $walletBalance = (float) ($author?->wallet_balance ?? 0.00);
        $totalWithdrawn = (float) ($author?->total_payout_withdrawn ?? 0.00);

        // 4. Pending Payout Requests
        $pendingPayoutAmount = AuthorPayoutRequest::where('user_id', $user->id)
            ->where('status', 'pending')
            ->sum('amount');

        $availableBalance = max(0, $walletBalance - $pendingPayoutAmount);

        // 5. Recent Royalties
        $recentRoyalties = (clone $royaltiesQuery)->with(['ebook', 'order'])
            ->latest('id')
            ->take(10)
            ->get();

        // 6. Recent Ebooks
        $recentEbooks = (clone $ebooksQuery)->latest('id')->take(5)->get();

        // 7. IdeaPatra (Blog Articles) Stats & Recent Posts
        $postsQuery = BlogPost::where(function ($q) use ($user, $author) {
            $q->where('submitted_by', $user->id)
              ->orWhere('author_id', $user->id);
            if ($author) {
                $q->orWhere('author_id', $author->id);
            }
        });

        $totalPosts = (clone $postsQuery)->count();
        $publishedPosts = (clone $postsQuery)->where('status', 'published')->count();
        $pendingPosts = (clone $postsQuery)->where('status', 'pending')->count();
        $draftPosts = (clone $postsQuery)->where('status', 'draft')->count();
        $recentPosts = (clone $postsQuery)->with('category')->latest('id')->take(5)->get();

        return view('author.dashboard', compact(
            'author',
            'user',
            'totalEbooks',
            'publishedEbooks',
            'pendingEbooks',
            'rejectedEbooks',
            'totalRoyaltyEarned',
            'totalCopiesSold',
            'totalHonorariumEarned',
            'totalHonorariumCount',
            'recentHonorariums',
            'walletBalance',
            'totalWithdrawn',
            'pendingPayoutAmount',
            'availableBalance',
            'recentRoyalties',
            'recentEbooks',
            'totalPosts',
            'publishedPosts',
            'pendingPosts',
            'draftPosts',
            'recentPosts'
        ));
    }

    /**
     * Display Author Royalty Earnings Ledger.
     */
    public function royalties(Request $request): View
    {
        $user = auth()->user();
        $author = $user->getAuthorRecord();

        $query = AuthorRoyalty::where(function ($q) use ($user, $author) {
            $q->where('user_id', $user->id);
            if ($author) {
                $q->orWhere('author_id', $author->id);
            }
        })->with(['ebook', 'order']);

        if ($request->filled('ebook_id')) {
            $query->where('ebook_id', $request->ebook_id);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $royalties = $query->latest('id')->paginate(20)->withQueryString();

        $totalEarned = AuthorRoyalty::where(function ($q) use ($user, $author) {
            $q->where('user_id', $user->id);
            if ($author) {
                $q->orWhere('author_id', $author->id);
            }
        })->where('status', '!=', 'refunded')->sum('royalty_amount');

        $authorEbooks = Ebook::where(function ($q) use ($user, $author) {
            $q->where('author_user_id', $user->id);
            if ($author) {
                $q->orWhere('author_id', $author->id);
            }
        })->get(['id', 'title']);

        return view('author.royalties', compact('author', 'royalties', 'totalEarned', 'authorEbooks'));
    }

    /**
     * Display Author IdeaPatra Reader Honorariums Ledger (পড়ে ভালো লাগা সম্মানি).
     */
    public function honorariums(Request $request): View
    {
        $user = auth()->user();
        $author = $user->getAuthorRecord();

        $query = AuthorHonorarium::where(function ($q) use ($user, $author) {
            $q->where('author_user_id', $user->id);
            if ($author) {
                $q->orWhere('author_id', $author->id);
            }
        })->with(['post', 'donor']);

        if ($request->filled('post_id')) {
            $query->where('blog_post_id', $request->post_id);
        }

        if ($request->filled('status')) {
            $query->where('payment_status', $request->status);
        }

        if ($request->filled('search')) {
            $search = trim($request->string('search'));
            $query->where(function ($q) use ($search) {
                $q->where('donor_name', 'like', "%{$search}%")
                  ->orWhere('donor_phone', 'like', "%{$search}%")
                  ->orWhere('message', 'like', "%{$search}%")
                  ->orWhere('trx_id', 'like', "%{$search}%");
            });
        }

        $honorariums = $query->latest('id')->paginate(20)->withQueryString();

        // Summary metrics
        $baseQuery = AuthorHonorarium::where(function ($q) use ($user, $author) {
            $q->where('author_user_id', $user->id);
            if ($author) {
                $q->orWhere('author_id', $author->id);
            }
        })->where('payment_status', 'completed');

        $totalEarned = (clone $baseQuery)->sum('author_amount');
        $totalCount = (clone $baseQuery)->count();
        $thisMonthSum = (clone $baseQuery)
            ->whereYear('created_at', now()->year)
            ->whereMonth('created_at', now()->month)
            ->sum('author_amount');
        $maxTip = (clone $baseQuery)->max('amount') ?? 0.00;

        $authorPosts = BlogPost::where(function ($q) use ($user, $author) {
            $q->where('submitted_by', $user->id)
              ->orWhere('author_id', $user->id);
            if ($author) {
                $q->orWhere('author_id', $author->id);
            }
        })->get(['id', 'title']);

        return view('author.honorariums', compact(
            'author',
            'honorariums',
            'totalEarned',
            'totalCount',
            'thisMonthSum',
            'maxTip',
            'authorPosts'
        ));
    }

    /**
     * Dynamically update author profile avatar (supports cropped base64 & standard file upload).
     */
    public function updateAvatar(Request $request)
    {
        $request->validate([
            'avatar'         => ['nullable', 'image', 'mimes:jpeg,png,jpg,webp', 'max:10240'],
            'avatar_cropped' => ['nullable', 'string'],
        ]);

        $user = auth()->user();
        $author = $user->getAuthorRecord();
        $avatarPath = null;

        // 1. Check if Base64 cropped photo sent from mobile studio
        if ($request->filled('avatar_cropped') && str_starts_with($request->input('avatar_cropped'), 'data:image')) {
            try {
                $croppedData = $request->input('avatar_cropped');
                @list($typeInfo, $data) = explode(';', $croppedData);
                @list(, $data)          = explode(',', $data);
                $decodedData = base64_decode($data);
                if ($decodedData !== false) {
                    $filename = 'avatars/author_' . $user->id . '_' . time() . '_' . uniqid() . '.jpg';
                    \Illuminate\Support\Facades\Storage::disk('public')->put($filename, $decodedData);
                    $avatarPath = $filename;
                }
            } catch (\Throwable $e) {
                \Illuminate\Support\Facades\Log::warning("Could not process dynamic cropped author avatar: " . $e->getMessage());
            }
        }

        // 2. Fallback to direct file upload
        if (!$avatarPath && $request->hasFile('avatar')) {
            $avatarPath = $request->file('avatar')->store('avatars', 'public');
        }

        if (!$avatarPath) {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['success' => false, 'message' => 'কোনো ছবি পাওয়া যায়নি। অনুগ্রহ করে একটি ছবি নির্বাচন করুন।'], 422);
            }
            return back()->with('error', 'কোনো ছবি পাওয়া যায়নি।');
        }

        // Update User
        $user->avatar = $avatarPath;
        $regData = is_array($user->reg_data) ? $user->reg_data : [];
        $regData['avatar'] = $avatarPath;
        $user->reg_data = $regData;
        $user->save();

        // Update Author Directory Record
        if ($author) {
            $author->avatar = $avatarPath;
            $author->author_image = $avatarPath;
            $author->save();
        }

        $fullUrl = asset('storage/' . ltrim($avatarPath, '/'));

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success'    => true,
                'message'    => 'প্রোফাইল ছবি সফলভাবে আপডেট ও সেভ হয়েছে!',
                'avatar_url' => $fullUrl,
                'avatar_path'=> $avatarPath,
            ]);
        }

        return back()->with('success', 'প্রোফাইল ছবি সফলভাবে আপডেট হয়েছে!');
    }

    /**
     * Update Author Literary Profile & Bio
     */
    public function updateProfile(Request $request)
    {
        $request->validate([
            'name'     => ['required', 'string', 'max:255'],
            'pen_name' => ['nullable', 'string', 'max:255'],
            'bio'      => ['nullable', 'string', 'max:5000'],
        ]);

        $user = auth()->user();
        $author = $user->getAuthorRecord();

        $user->name = $request->input('name');
        $regData = is_array($user->reg_data) ? $user->reg_data : [];
        if ($request->filled('pen_name')) {
            $regData['pen_name'] = $request->input('pen_name');
        }
        if ($request->has('bio')) {
            $regData['bio'] = $request->input('bio');
        }
        $user->reg_data = $regData;
        $user->save();

        if ($author) {
            $author->name = $request->filled('pen_name') ? $request->input('pen_name') : $request->input('name');
            $author->bio = $request->input('bio');
            $author->save();
        }

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'লেখক পরিচিতি ও তথ্য সফলভাবে আপডেট হয়েছে!',
            ]);
        }

        return back()->with('success', 'লেখক পরিচিতি ও তথ্য সফলভাবে আপডেট হয়েছে!');
    }
}
