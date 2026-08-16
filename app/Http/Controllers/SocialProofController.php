<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Order;

class SocialProofController extends Controller
{
    public function getRecentOrders()
    {
        // Fetch up to 5 recent orders, or fallback to some dummy data if empty
        $orders = Order::with('book')
            ->latest()
            ->take(5)
            ->get()
            ->map(function ($order) {
                return [
                    'customer_name' => explode(' ', $order->customer_name)[0] ?? 'গ্রাহক',
                    'district'      => ucfirst($order->district ?? 'ঢাকা'),
                    'book_title'    => $order->book ? $order->book->title : 'বই',
                    'time_ago'      => $order->created_at ? $order->created_at->diffForHumans() : 'কিছুক্ষণ আগে'
                ];
            });

        // Fallback fake data for demo if no orders exist yet
        if ($orders->isEmpty()) {
            $orders = collect([
                ['customer_name' => 'রাকিব', 'district' => 'ঢাকা', 'book_title' => 'সমকালীন সাহিত্য', 'time_ago' => '৫ মিনিট আগে'],
                ['customer_name' => 'তানভীর', 'district' => 'চট্টগ্রাম', 'book_title' => 'ব্যবসায় কৌশল', 'time_ago' => '১ ঘন্টা আগে'],
                ['customer_name' => 'ফারহানা', 'district' => 'সিলেট', 'book_title' => 'ইসলামিক জীবনধারা', 'time_ago' => '২ ঘন্টা আগে'],
            ]);
        }

        return response()->json($orders);
    }
}
