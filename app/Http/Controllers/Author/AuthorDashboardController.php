<?php

namespace App\Http\Controllers\Author;

use App\Http\Controllers\Controller;
use App\Models\AuthorPayoutRequest;
use App\Models\AuthorRoyalty;
use Illuminate\Http\Request;
use Illuminate\View\View;
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

        // 2. Royalty Stats
        $royaltiesQuery = AuthorRoyalty::where(function ($q) use ($user, $author) {
            $q->where('user_id', $user->id);
            if ($author) {
                $q->orWhere('author_id', $author->id);
            }
        });

        $totalRoyaltyEarned = (clone $royaltiesQuery)->where('status', '!=', 'refunded')->sum('royalty_amount');
        $totalCopiesSold = (clone $royaltiesQuery)->where('status', '!=', 'refunded')->count();

        $walletBalance = (float) ($author?->wallet_balance ?? 0.00);
        $totalWithdrawn = (float) ($author?->total_payout_withdrawn ?? 0.00);

        // 3. Pending Payout Requests
        $pendingPayoutAmount = AuthorPayoutRequest::where('user_id', $user->id)
            ->where('status', 'pending')
            ->sum('amount');

        $availableBalance = max(0, $walletBalance - $pendingPayoutAmount);

        // 4. Recent Royalties
        $recentRoyalties = (clone $royaltiesQuery)->with(['ebook', 'order'])
            ->latest('id')
            ->take(10)
            ->get();

        // 5. Recent Ebooks
        $recentEbooks = (clone $ebooksQuery)->latest('id')->take(5)->get();

        // 6. IdeaPatra (Blog Articles) Stats & Recent Posts
        $postsQuery = \Modules\Blog\Models\BlogPost::where(function ($q) use ($user, $author) {
            $q->where('submitted_by', $user->id);
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
}
