<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Order;

class UserController extends Controller
{
    public function dashboard()
    {
        $user = auth()->user();
        
        $myOrders = Order::where('user_id', $user->id)
            ->latest()
            ->take(5)
            ->get();
            
        $affiliateOrders = Order::where('affiliate_id', $user->id)
            ->latest()
            ->get();
            
        $totalCommissionEarned = $affiliateOrders->sum('commission_amount');
            
        return view('frontend.pages.my-account', compact('user', 'myOrders', 'affiliateOrders', 'totalCommissionEarned'));
    }
}
