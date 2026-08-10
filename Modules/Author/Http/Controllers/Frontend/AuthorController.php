<?php

namespace Modules\Author\Http\Controllers\Frontend;

use Illuminate\Routing\Controller;
use Modules\Author\Models\Author;

class AuthorController extends Controller
{
    public function index()
    {
        $authors = Author::where('is_active', true)->where('is_verified', true)->paginate(12);
        return view('author::index', compact('authors'));
    }

    public function show($slug)
    {
        $author = Author::where('slug', $slug)->where('is_active', true)->firstOrFail();
        $posts = $author->blogPosts()->published()->paginate(9);
        return view('author::show', compact('author', 'posts'));
    }

    public function register()
    {
        return view('author::register');
    }

    public function storeRegistration()
    {
        $validated = request()->validate([
            'name' => 'required|unique:authors',
            'email' => 'required|email|unique:authors',
            'bio' => 'required',
            'phone' => 'nullable',
            'website' => 'nullable|url',
        ]);

        $validated['slug'] = \Illuminate\Support\Str::slug($validated['name']);
        Author::create($validated);

        return redirect('/')->with('success', 'লেখক হিসেবে সফলভাবে নিবন্ধন সম্পন্ন হয়েছে।');
    }
}
