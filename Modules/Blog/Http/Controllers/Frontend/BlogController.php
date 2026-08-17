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
                ->orderBy('sort_order')
                ->get();
        }

        return view('blog::index', compact('posts', 'categories', 'featured', 'heroPost', 'categoriesWithPosts'));
    }

    public function show($slug)
    {
        $post = BlogPost::where('slug', $slug)->published()->firstOrFail();
        $post->increment('view_count');

        $related = BlogPost::published()
            ->where('category_id', $post->category_id)
            ->where('id', '!=', $post->id)
            ->limit(4)
            ->get();

        return view('blog::show', compact('post', 'related'));
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
