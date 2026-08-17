<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Order;
use Modules\Blog\Models\BlogPost;
use Modules\Blog\Models\BlogCategory;

class UserController extends Controller
{
    public function dashboard(Request $request)
    {
        $user = auth()->user();
        
        $myOrders = Order::where('user_id', $user->id)
            ->latest()
            ->take(10)
            ->get();
            
        $affiliateOrders = Order::where('affiliate_id', $user->id)
            ->latest()
            ->get();
            
        $totalCommissionEarned = $affiliateOrders->sum('commission_amount');

        // Author blog data
        $authorPosts = collect();
        $blogCategories = collect();
        $editPost = null;

        if ($user->role === 'author' || $user->reg_type === 'author' || BlogPost::where('author_id', $user->id)->exists()) {
            $authorPosts = BlogPost::where(function($q) use ($user) {
                    $q->where('author_id', $user->id)->orWhere('submitted_by', $user->id);
                })
                ->with('category')
                ->latest('id')
                ->get();

            $blogCategories = BlogCategory::where('is_active', true)->orderBy('name')->get();

            if ($request->filled('edit_post_id')) {
                $candidate = BlogPost::where('id', $request->edit_post_id)
                    ->where(function($q) use ($user) {
                        $q->where('author_id', $user->id)->orWhere('submitted_by', $user->id);
                    })->first();
                if ($candidate && ($candidate->status === 'draft' || $candidate->status === 'rejected' || $candidate->mod_status === 'rejected')) {
                    $editPost = $candidate;
                }
            }
        }
            
        return view('frontend.pages.my-account', compact(
            'user',
            'myOrders',
            'affiliateOrders',
            'totalCommissionEarned',
            'authorPosts',
            'blogCategories',
            'editPost'
        ));
    }
}
