<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Order;
use Modules\Book\Models\Book;

class OrderController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'book_id' => 'required|exists:books,id',
            'customer_name' => 'required|string|max:255',
            'customer_phone' => 'required|string|max:20',
            'district' => 'required|string',
            'customer_address' => 'required|string',
            'is_gift' => 'nullable|boolean',
            'gift_recipient_name' => 'nullable|required_if:is_gift,1|string|max:255',
            'gift_recipient_phone' => 'nullable|required_if:is_gift,1|string|max:20',
            'gift_recipient_address' => 'nullable|required_if:is_gift,1|string',
            'gift_message' => 'nullable|string',
        ]);

        $book = Book::findOrFail($validated['book_id']);
        $total = $book->discount_price ?? $book->price;
        
        // Add delivery fee
        if ($validated['district'] === 'dhaka') {
            $total += 50;
        } elseif ($validated['district'] === 'dhaka_sub') {
            $total += 100;
        } else {
            $total += 120;
        }

        $validated['total_amount'] = $total;
        $validated['user_id'] = auth()->id();
        
        if (isset($validated['is_gift']) && $validated['is_gift']) {
            $validated['total_amount'] += 20; // Example gift wrap fee
        }

        Order::create($validated);

        return back()->with('success', 'আপনার অর্ডারটি সফলভাবে গ্রহণ করা হয়েছে!');
    }
}
