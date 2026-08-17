<?php

namespace Modules\Blog\Http\Controllers\Frontend;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Blog\Models\BlogPost;
use Modules\Blog\Models\BlogCategory;
use Modules\Blog\Models\BlogTag;
use Illuminate\Support\Facades\DB;

class BlogController extends Controller
{
    public function index(Request $request)
    {
        $query = BlogPost::with(['category', 'author', 'tags'])
            ->where('status', 'published');

        if ($request->filled('search')) {
            $search = $request->string('search')->trim()->value();
            $query->where(function ($sub) use ($search) {
                $sub->where('title', 'like', "%{$search}%")
                    ->orWhere('content', 'like', "%{$search}%")
                    ->orWhere('excerpt', 'like', "%{$search}%");
            });
        }

        if ($request->filled('category')) {
            $query->whereHas('category', function ($q) use ($request) {
                $q->where('slug', $request->category);
            });
        }

        if ($request->sort === 'popular') {
            $query->orderByDesc('view_count');
        } else {
            $query->orderByDesc('published_at')->latest('id');
        }

        $posts = $query->paginate(12)->withQueryString();
        
        $categories = BlogCategory::withCount(['posts' => function($q) {
            $q->where('status', 'published');
        }])->where('is_active', true)->get();

        $featured = BlogPost::with(['category', 'author'])
            ->where('status', 'published')
            ->where(function($q) {
                $q->where('is_featured', true)->orWhereNotNull('featured_image');
            })
            ->latest('published_at')
            ->limit(5)
            ->get();

        // Hero post (the top featured post)
        $heroPost = $featured->first() ?: $posts->first();

        // Category-wise grouped posts for modern literary magazine structure
        $categoriesWithPosts = collect();
        if (!$request->filled('search') && !$request->filled('category')) {
            $categoriesWithPosts = BlogCategory::where('is_active', true)
                ->whereHas('posts', function($q) {
                    $q->where('status', 'published');
                })
                ->with(['posts' => function($q) {
                    $q->where('status', 'published')
                      ->with(['author', 'category'])
                      ->latest('published_at')
                      ->latest('id')
                      ->take(6);
                }])
                ->withCount(['posts' => function($q) {
                    $q->where('status', 'published');
                }])
                ->orderBy('id')
                ->get();
        }

        return view('blog::index', compact('posts', 'categories', 'featured', 'heroPost', 'categoriesWithPosts'));
    }

    public function show($slug)
    {
        $post = BlogPost::with(['category', 'author', 'tags', 'reviews.user'])
            ->where('slug', $slug)
            ->orWhere('id', $slug)
            ->first();

        if (!$post) {
            $decoded = urldecode($slug);
            $post = BlogPost::with(['category', 'author', 'tags', 'reviews.user'])
                ->where('slug', $decoded)
                ->orWhere('slug', 'like', "%{$decoded}%")
                ->orWhere('title', 'like', "%{$decoded}%")
                ->first();
        }

        if (!$post) {
            return redirect()->route('blog.index')->with('info', 'অনুরোধকৃত লেখাটি পাওয়া যায়নি বা সরানো হয়েছে। আইডিয়া সাহিত্যপত্রের সাম্প্রতিক লেখাগুলো নিচে দেখতে পারেন।');
        }

        // If post is not published yet, allow preview only for the author or admin
        if ($post->status !== 'published') {
            if (!auth()->check() || (auth()->id() !== $post->author_id && !auth()->user()->isAdmin())) {
                return redirect()->route('blog.index')->with('warning', 'এই লেখাটি এখনো পর্যালোচনার অপেক্ষায় রয়েছে এবং প্রকাশিত হয়নি।');
            }
        }

        $post->increment('view_count');

        $related = BlogPost::published()
            ->where('category_id', $post->category_id)
            ->where('id', '!=', $post->id)
            ->limit(4)
            ->get();

        return view('blog::show', compact('post', 'related'));
    }

    /**
     * Store reader comment / review for a blog post.
     */
    public function storeReview(Request $request, $id)
    {
        if (!auth()->check()) {
            session()->put('url.intended', url()->previous());
            return redirect()->route('login')->with('info', 'মন্তব্য বা রিভিউ দিতে অনুগ্রহ করে আপনার অ্যাকাউন্টে লগইন করুন।');
        }

        $request->validate([
            'comment' => 'required|string|max:2000',
            'rating'  => 'nullable|integer|min:1|max:5',
        ]);

        $post = BlogPost::findOrFail($id);

        \Modules\Review\Models\Review::create([
            'user_id'      => auth()->id(),
            'blog_post_id' => $post->id,
            'rating'       => (int) ($request->input('rating', 5)),
            'title'        => $request->input('title', 'পাঠক প্রতিক্রিয়া'),
            'comment'      => $request->input('comment'),
            'is_approved'  => true,
        ]);

        return redirect()->back()->with('success', 'আপনার সুন্দর মন্তব্য ও প্রতিক্রিয়া সফলভাবে প্রকাশিত হয়েছে। ধন্যবাদ!');
    }

    public function category($slug)
    {
        $category = BlogCategory::where('slug', $slug)->firstOrFail();
        $posts = $category->posts()->published()->paginate(12);

        return view('blog::category', compact('category', 'posts'));
    }

    public function tag($slug)
    {
        $tag = BlogTag::where('slug', $slug)->firstOrFail();
        $posts = $tag->posts()->published()->paginate(12);

        return view('blog::tag', compact('tag', 'posts'));
    }
}
