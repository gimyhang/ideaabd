<?php

namespace Modules\Blog\Http\Controllers\Admin;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Blog\Models\BlogPost;
use Modules\Blog\Models\BlogCategory;
use Illuminate\Support\Str;

class BlogController extends Controller
{
    public function index()
    {
        $posts = BlogPost::with(['category', 'author'])->paginate(15);
        return view('blog::admin.index', compact('posts'));
    }

    public function create()
    {
        $categories = BlogCategory::where('is_active', true)->get();
        return view('blog::admin.create', compact('categories'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required',
            'excerpt' => 'nullable',
            'category_id' => 'nullable|exists:blog_categories,id',
            'featured_image' => 'nullable|image',
        ]);

        $baseSlug = Str::slug($validated['title']) ?: 'post-' . Str::random(6);
        $slug = $baseSlug;
        $counter = 1;
        while (BlogPost::withTrashed()->where('slug', $slug)->exists()) {
            $slug = $baseSlug . '-' . (++$counter);
        }
        $validated['slug'] = $slug;
        $validated['author_id'] = auth()->id();

        if ($request->hasFile('featured_image')) {
            $validated['featured_image'] = \App\Services\ImageOptimizerService::convertAndStore($request->file('featured_image'), 'blog', 'public', 82, 1400, 900);
        }

        BlogPost::create($validated);

        return redirect()->route('admin.blog.posts.index')->with('success', 'পোস্ট সফলভাবে তৈরি হয়েছে।');
    }

    public function edit(BlogPost $post)
    {
        $categories = BlogCategory::where('is_active', true)->get();
        return view('blog::admin.edit', compact('post', 'categories'));
    }

    public function update(Request $request, BlogPost $post)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required',
            'excerpt' => 'nullable',
            'category_id' => 'nullable|exists:blog_categories,id',
            'featured_image' => 'nullable|image',
        ]);

        if ($request->hasFile('featured_image')) {
            $validated['featured_image'] = \App\Services\ImageOptimizerService::convertAndStore($request->file('featured_image'), 'blog', 'public', 82, 1400, 900);
        }

        $post->update($validated);

        return redirect()->route('admin.blog.posts.index')->with('success', 'পোস্ট সফলভাবে আপডেট হয়েছে।');
    }

    public function destroy(BlogPost $post)
    {
        $post->delete();
        return redirect()->route('admin.blog.posts.index')->with('success', 'পোস্ট সফলভাবে ডিলিট হয়েছে।');
    }

    public function publish(BlogPost $post)
    {
        $post->update([
            'status' => 'published',
            'published_at' => now(),
        ]);

        // Send email notification to author
        if ($post->author_id) {
            $author = \App\Models\User::find($post->author_id);
            if ($author && $author->email && !str_ends_with($author->email, '@buyer.ideaabd.com')) {
                try {
                    \Illuminate\Support\Facades\Mail::to($author->email)->send(new \App\Mail\BlogPostApprovedMail($post, $author));
                } catch (\Throwable $e) {
                    \Illuminate\Support\Facades\Log::warning("Could not send blog post approval email: " . $e->getMessage());
                }
            }
        }

        return back()->with('success', 'পোস্ট প্রকাশিত হয়েছে এবং লেখককে ইমেইল নোটিফিকেশন পাঠানো হয়েছে।');
    }

    public function unpublish(BlogPost $post)
    {
        $post->update([
            'status' => 'draft',
            'published_at' => null,
        ]);

        return back()->with('success', 'পোস্ট অপ্রকাশিত করা হয়েছে।');
    }

    public function feature(BlogPost $post)
    {
        $post->update(['is_featured' => true]);
        return back()->with('success', 'পোস্ট ফিচার করা হয়েছে।');
    }

    public function unfeature(BlogPost $post)
    {
        $post->update(['is_featured' => false]);
        return back()->with('success', 'পোস্ট আনফিচার করা হয়েছে।');
    }
}
