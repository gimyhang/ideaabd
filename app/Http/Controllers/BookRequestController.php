<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\BookRequest;

class BookRequestController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'customer_name' => 'nullable|string|max:255',
            'customer_phone' => 'nullable|string|max:50',
            'book_title' => 'required|string|max:255',
            'author_name' => 'nullable|string|max:255',
            'additional_info' => 'nullable|string',
        ]);

        BookRequest::create($validated);

        return back()->with('success', 'আপনার রিকোয়েস্ট সফলভাবে জমা দেওয়া হয়েছে! আমরা দ্রুত বইটি সংগ্রহ করার চেষ্টা করব।');
    }

    public function index()
    {
        $requests = BookRequest::latest()->paginate(20);
        return view('admin.book-requests', compact('requests'));
    }

    public function updateStatus(Request $request, $id)
    {
        $request->validate(['status' => 'required|in:pending,processing,available,closed']);
        $bookRequest = BookRequest::findOrFail($id);
        $bookRequest->update(['status' => $request->status]);
        return back()->with('success', 'স্ট্যাটাস আপডেট করা হয়েছে।');
    }
}
