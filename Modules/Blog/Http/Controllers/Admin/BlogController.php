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
            'title' => 'required|unique:blog_posts',
            'content' => 'required',
            'excerpt' => 'nullable',
            'category_id' => 'nullable|exists:blog_categories,id',
            'featured_image' => 'nullable|image',
        ]);

        $validated['slug'] = Str::slug($validated['title']);
        $validated['author_id'] = auth()->id();

        if ($request->hasFile('featured_image')) {
            $validated['featured_image'] = $request->file('featured_image')->store('blog', 'public');
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
            'title' => 'required|unique:blog_posts,title,' . $post->id,
            'content' => 'required',
            'excerpt' => 'nullable',
            'category_id' => 'nullable|exists:blog_categories,id',
            'featured_image' => 'nullable|image',
        ]);

        if ($request->hasFile('featured_image')) {
            $validated['featured_image'] = $request->file('featured_image')->store('blog', 'public');
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

        return back()->with('success', 'পোস্ট প্রকাশিত হয়েছে।');
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
