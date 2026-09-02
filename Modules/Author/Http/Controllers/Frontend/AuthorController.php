<?php

namespace Modules\Author\Http\Controllers\Frontend;

use Illuminate\Routing\Controller;
use Modules\Author\Models\Author;

class AuthorController extends Controller
{
    public function index()
    {
        return app(\App\Http\Controllers\AuthorController::class)->index(request());
    }

    public function show($slug)
    {
        return app(\App\Http\Controllers\AuthorController::class)->show($slug);
    }

    public function register()
    {
        return view('author::register');
    }

    public function storeRegistration()
    {
        $validated = request()->validate([
            'name'    => 'required|string|max:255',
            'email'   => 'nullable|email|max:255',
            'bio'     => 'nullable|string|max:3000',
            'phone'   => 'nullable|string|max:30',
            'website' => 'nullable|url|max:255',
        ]);

        $author = Author::findOrCreateUnified([
            'name'        => $validated['name'],
            'email'       => $validated['email'] ?? null,
            'phone'       => $validated['phone'] ?? null,
            'bio'         => $validated['bio'] ?? null,
            'website'     => $validated['website'] ?? null,
            'is_active'   => false,
            'is_verified' => false,
        ]);

        return redirect('/')->with('success', 'লেখক হিসেবে আপনার নিবন্ধন আবেদন সফলভাবে জমা হয়েছে। অ্যাডমিন পর্যালোচনা ও অনুমোদনের পর প্রোফাইলটি সক্রিয় হবে।');
    }
}
