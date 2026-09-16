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

        $author = method_exists($user, 'getAuthorRecord') ? $user->getAuthorRecord() : null;

        $authorPostsQuery = BlogPost::where(function($q) use ($user, $author) {
            $q->where('author_id', $user->id)
              ->orWhere('submitted_by', $user->id);

            if ($author) {
                $q->orWhere('author_id', $author->id);
            }

            $phones = array_unique(array_filter([$user->phone ?? null, $author->phone ?? null]));
            if (!empty($phones)) {
                $q->orWhereIn('owner_phone', $phones);
            }

            $names = array_unique(array_filter([$user->name ?? null, $author->name ?? null]));
            if (!empty($names)) {
                $q->orWhereIn('owner_name', $names);
            }
        });

        if ($user->role === 'author' || $user->reg_type === 'author' || $author || (clone $authorPostsQuery)->exists()) {
            $authorPosts = (clone $authorPostsQuery)
                ->with('category')
                ->latest('id')
                ->get();

            $blogCategories = BlogCategory::where('is_active', true)->orderBy('name')->get();

            if ($request->filled('edit_post_id')) {
                $candidate = (clone $authorPostsQuery)
                    ->where('id', $request->edit_post_id)
                    ->first();
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

        // 8. Recommended Books for Account Hub Carousel
        $recommendedBooks = collect();
        if (class_exists(\Modules\Book\Models\Book::class)) {
            try {
                $recommendedBooks = \Modules\Book\Models\Book::where('is_active', true)
                    ->inRandomOrder()
                    ->take(8)
                    ->get();
            } catch (\Throwable $e) {
                $recommendedBooks = collect();
            }
        }

        // 9. Continue Reading (Most recently read/accessed eBook)
        $recentReading = $myEbooks->first();

        // 10. Recent Orders (Top 3 for dashboard dual widget)
        $recentOrders = Order::where('user_id', $user->id)->with('book')->latest('id')->take(3)->get();

        // 11. Author Published Books Count
        $publishedBooksCount = 0;
        if (class_exists(\Modules\Book\Models\Book::class)) {
            try {
                if ($author) {
                    $publishedBooksCount = \Modules\Book\Models\Book::where(function($q) use ($author, $user) {
                        $q->where('author_link_id', $author->id)
                          ->orWhere('author_name', $user->name);
                    })->count();
                } elseif ($user->role === 'author' || $user->reg_type === 'author') {
                    $publishedBooksCount = \Modules\Book\Models\Book::where('author_name', $user->name)->count();
                }
            } catch (\Throwable $e) {
                $publishedBooksCount = 0;
            }
        }

        // 12. KYC Checklist & Percentage Calculation
        $regData = is_array($user->reg_data) ? $user->reg_data : [];
        $kycItems = [
            'photo'   => !empty($user->avatar) || !empty($regData['avatar']),
            'name'    => !empty($user->name) || !empty($regData['name_bn']) || !empty($regData['name_en']),
            'bio'     => !empty($regData['bio']),
            'nid'     => !empty($regData['nid']) || !empty($regData['nid_file']),
            'address' => !empty($regData['address']) || !empty($defaultAddress['address']) || !empty($regData['district']),
        ];
        $kycCompletedCount = count(array_filter($kycItems));
        $kycPercent = (int) round(($kycCompletedCount / max(1, count($kycItems))) * 100);

        // 13. Wallet & Royalty Balance
        $walletBalance = (float) ($author?->wallet_balance ?? 0);
        if ($walletBalance <= 0 && $totalCommissionEarned > 0) {
            $walletBalance = (float) $totalCommissionEarned;
        }

        $monthlyRoyalty = 0.0;
        if (class_exists(\App\Models\AuthorRoyalty::class)) {
            try {
                $monthlyRoyalty += (float) \App\Models\AuthorRoyalty::where('user_id', $user->id)
                    ->whereMonth('created_at', now()->month)
                    ->whereYear('created_at', now()->year)
                    ->sum('royalty_amount');
            } catch (\Throwable $e) {}
        }
        if (class_exists(\App\Models\AuthorHonorarium::class)) {
            try {
                $monthlyRoyalty += (float) \App\Models\AuthorHonorarium::where('author_user_id', $user->id)
                    ->where('payment_status', 'completed')
                    ->whereMonth('created_at', now()->month)
                    ->whereYear('created_at', now()->year)
                    ->sum('author_amount');
            } catch (\Throwable $e) {}
        }

        return view('frontend.pages.my-account', compact(
            'user',
            'myOrders',
            'recentOrders',
            'totalOrdersCount',
            'deliveredOrdersCount',
            'totalSpentAmount',
            'pointsEarnedTotal',
            'wishlistItems',
            'affiliateOrders',
            'totalCommissionEarned',
            'author',
            'authorPosts',
            'blogCategories',
            'editPost',
            'defaultAddress',
            'myEbooks',
            'recentReading',
            'recommendedBooks',
            'publishedBooksCount',
            'kycItems',
            'kycPercent',
            'walletBalance',
            'monthlyRoyalty'
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
            $updates['avatar'] = \App\Services\ImageOptimizerService::convertAndStore($request->file('avatar'), 'avatars', 'public', 85, 600, 600);
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

    /**
     * Update Role-based KYC & Verification Profile (Author, Publisher, Seller, Reader).
     */
    public function updateKyc(Request $request)
    {
        $user = auth()->user();
        $role = $user->role ?: ($user->reg_type ?: 'buyer');

        $validated = $request->validate([
            'name'                   => 'nullable|string|max:255',
            'name_bn'                => 'nullable|string|max:255',
            'name_en'                => 'nullable|string|max:255',
            'pen_name'               => 'nullable|string|max:255',
            'avatar'                 => 'nullable|image|mimes:jpeg,png,jpg,webp|max:5120',
            'genres'                 => 'nullable|array',
            'genres.*'               => 'string|max:100',
            'bio'                    => 'nullable|string|max:5000',
            'nid'                    => 'nullable|string|max:50',
            'nid_file'               => 'nullable|file|mimes:jpeg,png,jpg,webp,pdf|max:10240',
            'payout_account_type'    => 'nullable|string|max:50',
            'payout_account_details' => 'nullable|string|max:255',
            'publisher_name'         => 'nullable|string|max:255',
            'established'            => 'nullable|digits:4',
            'trade_license'          => 'nullable|string|max:100',
            'shop_name'              => 'nullable|string|max:255',
            'address'                => 'nullable|string|max:500',
            'district'               => 'nullable|string|max:100',
            'thana'                  => 'nullable|string|max:100',
        ]);

        $regData = is_array($user->reg_data) ? $user->reg_data : [];

        // 1. Handle Avatar Upload
        if ($request->hasFile('avatar') && $request->file('avatar')->isValid()) {
            $avatarPath = $request->file('avatar')->store('avatars', 'public');
            $user->avatar = $avatarPath;
            $regData['avatar'] = $avatarPath;
        }

        // 2. Handle NID Document Upload
        if ($request->hasFile('nid_file') && $request->file('nid_file')->isValid()) {
            $nidPath = $request->file('nid_file')->store('kyc_documents', 'public');
            $regData['nid_file'] = $nidPath;
        }

        // 3. Populate KYC Meta Fields
        if (!empty($validated['name_bn'])) $regData['name_bn'] = $validated['name_bn'];
        if (!empty($validated['name_en'])) $regData['name_en'] = $validated['name_en'];
        if (!empty($validated['pen_name'])) $regData['pen_name'] = $validated['pen_name'];
        if (isset($validated['genres'])) $regData['genres'] = $validated['genres'];
        if (!empty($validated['bio'])) $regData['bio'] = $validated['bio'];
        if (!empty($validated['nid'])) $regData['nid'] = $validated['nid'];
        if (!empty($validated['payout_account_type'])) $regData['payout_type'] = $validated['payout_account_type'];
        if (!empty($validated['payout_account_details'])) $regData['payout_details'] = $validated['payout_account_details'];
        if (!empty($validated['publisher_name'])) $regData['publisher_name'] = $validated['publisher_name'];
        if (!empty($validated['established'])) $regData['established'] = $validated['established'];
        if (!empty($validated['trade_license'])) $regData['trade_license'] = $validated['trade_license'];
        if (!empty($validated['shop_name'])) $regData['shop_name'] = $validated['shop_name'];
        if (!empty($validated['address'])) $regData['address'] = $validated['address'];
        if (!empty($validated['district'])) $regData['district'] = $validated['district'];
        if (!empty($validated['thana'])) $regData['thana'] = $validated['thana'];

        $regData['kyc_submitted_at'] = now()->toIso8601String();

        // Update User Model
        if (!empty($validated['name_bn'])) {
            $user->name = $validated['name_bn'];
        } elseif (!empty($validated['name'])) {
            $user->name = $validated['name'];
        }

        $user->reg_data = $regData;
        if ($user->reg_status !== 'approved') {
            $user->reg_status = 'pending';
        }
        $user->save();

        // 4. Sync with Module Models (Author / Publisher)
        if (($role === 'author' || $user->reg_type === 'author') && class_exists(\Modules\Author\Models\Author::class)) {
            try {
                \Modules\Author\Models\Author::findOrCreateUnified([
                    'name'                   => $user->name,
                    'name_bn'                => $validated['name_bn'] ?? null,
                    'name_en'                => $validated['name_en'] ?? null,
                    'bio'                    => $validated['bio'] ?? null,
                    'avatar'                 => $user->avatar,
                    'email'                  => $user->email,
                    'phone'                  => $user->phone,
                    'user_id'                => $user->id,
                    'payout_account_type'    => $validated['payout_account_type'] ?? 'bkash',
                    'payout_account_details' => $validated['payout_account_details'] ?? null,
                ]);
            } catch (\Throwable $e) {
                \Illuminate\Support\Facades\Log::warning('Author KYC sync error: ' . $e->getMessage());
            }
        }

        return redirect()->route('my-account', ['tab' => 'kyc'])
            ->with('success', 'আপনার KYC ভেরিফিকেশন তথ্য সফলভাবে জমা দেওয়া হয়েছে! এডমিন পর্যালোচনার পর ভেরিফাইড ব্যাজ সক্রিয় হবে।');
    }
}

