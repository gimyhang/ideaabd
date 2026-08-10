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
        $query = BlogPost::published();

        if ($request->search) {
            $query->where('title', 'like', "%{$request->search}%")
                  ->orWhere('content', 'like', "%{$request->search}%");
        }

        if ($request->category) {
            $query->whereHas('category', function ($q) use ($request) {
                $q->where('slug', $request->category);
            });
        }

        if ($request->sort === 'latest') {
            $query->latest('published_at');
        } elseif ($request->sort === 'popular') {
            $query->orderBy('view_count', 'desc');
        } else {
            $query->latest('published_at');
        }

        $posts = $query->paginate(12);
        $categories = BlogCategory::where('is_active', true)->get();
        $featured = BlogPost::published()->featured()->limit(5)->get();

        return view('blog::index', compact('posts', 'categories', 'featured'));
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
