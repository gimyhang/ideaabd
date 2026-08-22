<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use App\Models\Order;
use App\Models\User;
use Modules\Blog\Models\BlogPost;
use Modules\Blog\Models\BlogCategory;
use Modules\Book\Models\Wishlist;

class UserController extends Controller
{
    public function dashboard(Request $request)
    {
        $user = auth()->user();

        // 1. Orders Query with Filtering
        $orderSearch = $request->string('order_search')->trim()->value();
        $orderStatus = $request->string('order_status')->trim()->value();

        $ordersQuery = Order::where('user_id', $user->id)
            ->with(['book'])
            ->when($orderSearch, function ($q, $term) {
                $like = '%' . $term . '%';
                $q->where(function ($w) use ($like) {
                    $w->where('order_number', 'like', $like)
                      ->orWhere('id', 'like', $like)
                      ->orWhereHas('book', fn($bq) => $bq->where('title', 'like', $like));
                });
            })
            ->when($orderStatus && $orderStatus !== 'all', fn ($q) => $q->where('status', $orderStatus));

        $myOrders = $ordersQuery->latest('id')->paginate(8, ['*'], 'orders_page')->withQueryString();

        // 2. Customer Summary KPIs
        $totalOrdersCount = Order::where('user_id', $user->id)->count();
        $deliveredOrdersCount = Order::where('user_id', $user->id)->where('status', 'delivered')->count();
        $totalSpentAmount = Order::where('user_id', $user->id)->whereNotIn('status', ['cancelled', 'rejected'])->sum('total_amount');
        $pointsEarnedTotal = Order::where('user_id', $user->id)->sum('points_earned');

        // 3. Wishlist Books
        $wishlistItems = collect();
        if (class_exists(Wishlist::class)) {
            try {
                $wishlistItems = Wishlist::where('user_id', $user->id)->with('book')->latest('id')->get();
            } catch (\Throwable $e) {
                $wishlistItems = collect();
            }
        }

        // 4. Affiliate Earnings
        $affiliateOrders = Order::where('affiliate_id', $user->id)->latest('id')->get();
        $totalCommissionEarned = $affiliateOrders->sum('commission_amount');

        // 5. Author blog data
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

        // 6. Default Shipping Info from Last Order or Reg Data
        $lastOrder = Order::where('user_id', $user->id)->latest('id')->first();
        $defaultAddress = [
            'name'     => $lastOrder?->customer_name ?: $user->name,
            'phone'    => $lastOrder?->customer_phone ?: $user->phone,
            'district' => $lastOrder?->district ?: ($user->reg_data['district'] ?? ''),
            'thana'    => $lastOrder?->thana ?: ($user->reg_data['thana'] ?? ''),
            'address'  => $lastOrder?->customer_address ?: ($user->reg_data['address'] ?? ''),
        ];

        // 7. User's E-Book Library (Strictly verified purchases and claimed free books)
        $myEbooks = collect();
        if (\Illuminate\Support\Facades\Schema::hasTable('user_ebook_library')) {
            $myEbooks = \App\Models\UserEbookLibrary::where('user_id', $user->id)
                ->with(['ebook.author', 'ebook.category'])
                ->where('is_active', true)
                ->where(function ($q) {
                    $q->where('access_type', 'purchased')
                      ->orWhereHas('ebook', fn ($eq) => $eq->where('price', '<=', 0));
                })
                ->latest('id')
                ->get();
        }

        return view('frontend.pages.my-account', compact(
            'user',
            'myOrders',
            'totalOrdersCount',
            'deliveredOrdersCount',
            'totalSpentAmount',
            'pointsEarnedTotal',
            'wishlistItems',
            'affiliateOrders',
            'totalCommissionEarned',
            'authorPosts',
            'blogCategories',
            'editPost',
            'defaultAddress',
            'myEbooks'
        ));
    }

    /**
     * Update customer profile name, email, phone, and avatar.
     */
    public function updateProfile(Request $request)
    {
        $user = auth()->user();

        $validated = $request->validate([
            'name'   => 'required|string|max:255',
            'phone'  => 'required|string|max:20|unique:users,phone,' . $user->id,
            'email'  => 'nullable|email|max:255|unique:users,email,' . $user->id,
            'avatar' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:4096',
        ], [
            'name.required'  => 'আপনার পুরো নাম লিখুন।',
            'phone.required' => 'মোবাইল নম্বর দেওয়া বাধ্যতামূলক।',
            'phone.unique'   => 'এই মোবাইল নম্বরটি অন্য অ্যাকাউন্টে ব্যবহৃত হচ্ছে।',
            'email.email'    => 'সঠিক ফরম্যাটের ইমেইল দিন।',
            'email.unique'   => 'এই ইমেইলটি অন্য অ্যাকাউন্টে ব্যবহৃত হচ্ছে।',
        ]);

        $updates = [
            'name'  => $validated['name'],
            'phone' => $validated['phone'],
        ];

        if (!empty($validated['email'])) {
            $updates['email'] = $validated['email'];
        }

        if ($request->hasFile('avatar')) {
            $updates['avatar'] = $request->file('avatar')->store('avatars', 'public');
        }

        $user->update($updates);

        return redirect()->route('my-account', ['tab' => 'settings'])
            ->with('success', 'আপনার প্রোফাইল তথ্য সফলভাবে হালনাগাদ করা হয়েছে!');
    }

    /**
     * Update customer default shipping address.
     */
    public function updateAddress(Request $request)
    {
        $user = auth()->user();

        $validated = $request->validate([
            'name'     => 'required|string|max:255',
            'phone'    => 'required|string|max:20',
            'district' => 'required|string|max:100',
            'thana'    => 'nullable|string|max:100',
            'address'  => 'required|string|max:500',
        ], [
            'name.required'     => 'প্রাপকের নাম প্রদান করুন।',
            'phone.required'    => 'যোগাযোগের মোবাইল নম্বর প্রদান করুন।',
            'district.required' => 'জেলা নির্বাচন করুন।',
            'address.required'  => 'পূর্ণাঙ্গ ঠিকানা লিখুন।',
        ]);

        $regData = is_array($user->reg_data) ? $user->reg_data : [];
        $regData['shipping_name']     = $validated['name'];
        $regData['shipping_phone']    = $validated['phone'];
        $regData['district']          = $validated['district'];
        $regData['thana']             = $validated['thana'] ?? '';
        $regData['address']           = $validated['address'];

        $user->reg_data = $regData;
        $user->save();

        return redirect()->route('my-account', ['tab' => 'address'])
            ->with('success', 'আপনার ডেলিভারি ঠিকানা সফলভাবে সংরক্ষণ করা হয়েছে!');
    }

    /**
     * Change customer password.
     */
    public function updatePassword(Request $request)
    {
        $user = auth()->user();

        $request->validate([
            'current_password' => 'required|string',
            'password'         => [
                'required',
                'confirmed',
                'string',
                'min:8',
                'max:25',
                'regex:/[!@#$%^&*(),.?":{}|<>_\-+=]/',
            ],
        ], [
            'current_password.required' => 'বর্তমান পাসওয়ার্ড প্রদান করুন।',
            'password.required'         => 'নতুন পাসওয়ার্ড প্রদান করুন।',
            'password.min'              => 'নতুন পাসওয়ার্ড সর্বনিম্ন ৮ অক্ষরের হতে হবে।',
            'password.regex'            => 'নতুন পাসওয়ার্ডে অন্তত একটি স্পেশাল ক্যারেক্টার (যেমন: @, #, $, %, !, *) থাকতে হবে।',
            'password.confirmed'        => 'নতুন পাসওয়ার্ড এবং নিশ্চিতকরণ পাসওয়ার্ড মেলেনি।',
        ]);

        if (!Hash::check($request->current_password, $user->password)) {
            return redirect()->route('my-account', ['tab' => 'security'])
                ->with('error', 'আপনার বর্তমান পাসওয়ার্ডটি সঠিক নয়!');
        }

        $user->password = Hash::make($request->password);
        $user->save();

        return redirect()->route('my-account', ['tab' => 'security'])
            ->with('success', 'পাসওয়ার্ড সফলভাবে পরিবর্তন করা হয়েছে!');
    }

    /**
     * Get single order details JSON for live tracking modal.
     */
    public function orderDetails($id)
    {
        $user = auth()->user();
        $order = Order::where('user_id', $user->id)->with('book')->findOrFail($id);

        return response()->json([
            'success' => true,
            'order'   => $order,
        ]);
    }

    /**
     * Remove item from wishlist.
     */
    public function removeFromWishlist($id)
    {
        $user = auth()->user();
        if (class_exists(Wishlist::class)) {
            Wishlist::where('user_id', $user->id)->where('id', $id)->delete();
        }

        return redirect()->route('my-account', ['tab' => 'wishlist'])
            ->with('success', 'বইটি পছন্দের তালিকা থেকে অপসারণ করা হয়েছে।');
    }
}
