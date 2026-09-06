<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Mail\UserApprovedMail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class RegistrationApprovalController extends Controller
{
    // List all registrations with rich filtering & stats
    public function index(Request $request)
    {
        $query = User::whereIn('role', ['seller', 'publisher', 'author']);

        // Status filter
        if ($request->filled('status')) {
            $query->where('reg_status', $request->status);
        }

        // Type / Role filter
        if ($request->filled('type')) {
            $query->where(function ($q) use ($request) {
                $q->where('reg_type', $request->type)
                  ->orWhere('role', $request->type);
            });
        }

        // Search filter
        if ($request->filled('search')) {
            $term = trim($request->search);
            $query->where(function ($q) use ($term) {
                $q->where('name', 'like', '%' . $term . '%')
                  ->orWhere('email', 'like', '%' . $term . '%')
                  ->orWhere('phone', 'like', '%' . $term . '%')
                  ->orWhere('reg_data', 'like', '%' . $term . '%');
            });
        }

        // Date range filter
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        // Sorting
        $sort = $request->input('sort', 'pending_first');
        match ($sort) {
            'latest'        => $query->latest('created_at'),
            'oldest'        => $query->oldest('created_at'),
            'name_asc'      => $query->orderBy('name', 'asc'),
            'name_desc'     => $query->orderBy('name', 'desc'),
            'pending_first' => $query->orderByRaw("CASE reg_status WHEN 'pending' THEN 0 WHEN 'approved' THEN 1 ELSE 2 END")->latest('created_at'),
            default         => $query->orderByRaw("CASE reg_status WHEN 'pending' THEN 0 WHEN 'approved' THEN 1 ELSE 2 END")->latest('created_at'),
        };

        $perPage = in_array((int) $request->input('per_page'), [10, 20, 25, 50, 100], true) ? (int) $request->input('per_page') : 20;
        $registrations = $query->paginate($perPage)->withQueryString();

        $baseRoleScope = User::whereIn('role', ['seller', 'publisher', 'author']);
        $counts = [
            'all'        => (clone $baseRoleScope)->count(),
            'pending'    => (clone $baseRoleScope)->where('reg_status', 'pending')->count(),
            'approved'   => (clone $baseRoleScope)->where('reg_status', 'approved')->count(),
            'rejected'   => (clone $baseRoleScope)->where('reg_status', 'rejected')->count(),
            'authors'    => User::where('role', 'author')->count(),
            'publishers' => User::where('role', 'publisher')->count(),
            'sellers'    => User::where('role', 'seller')->count(),
        ];

        return view('admin.registrations.index', compact('registrations', 'counts', 'sort', 'perPage'));
    }

    // Show individual registration detail page
    public function show(User $user)
    {
        return view('admin.registrations.show', compact('user'));
    }

    // Edit individual registration detail page
    public function edit(User $user)
    {
        return view('admin.registrations.edit', compact('user'));
    }

    // AJAX Details endpoint for popup modal preview
    public function details(User $user)
    {
        $regData = is_array($user->reg_data) ? $user->reg_data : [];
        $author = $user->getAuthorRecord();

        $rawAvatar = $user->avatar ?: ($author?->avatar ?: ($regData['avatar'] ?? null));
        $avatarUrl = null;
        if (!empty($rawAvatar)) {
            $avatarUrl = str_starts_with($rawAvatar, 'http') ? $rawAvatar : asset('storage/' . ltrim($rawAvatar, '/'));
        }

        if ($author) {
            if (empty($regData['bio']) && !empty($author->bio)) {
                $regData['bio'] = $author->bio;
            }
            if (empty($regData['pen_name']) && !empty($author->name) && $author->name !== $user->name) {
                $regData['pen_name'] = $author->name;
            }
            if (empty($regData['name_bn']) && !empty($author->name_bn)) {
                $regData['name_bn'] = $author->name_bn;
            }
            if (empty($regData['website']) && !empty($author->website)) {
                $regData['website'] = $author->website;
            }
            if (empty($regData['payout_method']) && !empty($author->payout_account_type)) {
                $regData['payout_method'] = $author->payout_account_type;
            }
            if (empty($regData['payout_number']) && !empty($author->payout_account_details)) {
                $regData['payout_number'] = $author->payout_account_details;
            }
            if (!empty($author->social_links) && is_array($author->social_links)) {
                $regData['facebook'] = $regData['facebook'] ?? ($author->social_links['facebook'] ?? null);
                $regData['twitter']  = $regData['twitter'] ?? ($author->social_links['twitter'] ?? null);
                $regData['youtube']  = $regData['youtube'] ?? ($author->social_links['youtube'] ?? null);
            }
        }

        return response()->json([
            'success'               => true,
            'user'                  => [
                'id'                => $user->id,
                'name'              => $user->name,
                'email'             => $user->email,
                'phone'             => $user->phone,
                'role'              => $user->role,
                'reg_type'          => $user->reg_type ?? $user->role,
                'reg_status'        => $user->reg_status,
                'is_active'         => (bool) $user->is_active,
                'rejection_reason'  => $user->rejection_reason,
            ],
            'avatar_url'            => $avatarUrl,
            'reg_data'              => $regData,
            'author'                => $author ? [
                'id'            => $author->id,
                'name'          => $author->name,
                'name_bn'       => $author->name_bn,
                'slug'          => $author->slug,
                'bio'           => $author->bio,
                'is_active'     => $author->is_active,
                'is_verified'   => $author->is_verified,
            ] : null,
            'author_slug'           => $author?->slug,
            'author_id'             => $author?->id,
            'created_at_formatted'  => $user->created_at ? $user->created_at->format('d M Y, h:i A') : '',
            'approved_at_formatted' => $user->approved_at ? \Carbon\Carbon::parse($user->approved_at)->format('d M Y, h:i A') : null,
        ]);
    }

    // Update registration detail
    public function update(Request $request, User $user)
    {
        $validated = $request->validate([
            'name'           => ['required', 'string', 'max:255'],
            'name_bn'        => ['nullable', 'string', 'max:255'],
            'email'          => ['required', 'email', 'max:255', 'unique:users,email,' . $user->id],
            'phone'          => ['required', 'string', 'max:30', 'unique:users,phone,' . $user->id],
            'role'           => ['required', 'in:author,seller,publisher,buyer'],
            'reg_status'     => ['required', 'in:pending,approved,rejected'],
            'is_active'      => ['nullable', 'boolean'],
            'full_name'      => ['nullable', 'string', 'max:255'],
            'pen_name'       => ['nullable', 'string', 'max:255'],
            'genre'          => ['nullable', 'string', 'max:255'],
            'bio'            => ['nullable', 'string'],
            'nid'            => ['nullable', 'string', 'max:50'],
            'shop_name'      => ['nullable', 'string', 'max:255'],
            'zone'           => ['nullable', 'string', 'max:255'],
            'publisher_name' => ['nullable', 'string', 'max:255'],
            'address'        => ['nullable', 'string'],
            'trade_license'  => ['nullable', 'string', 'max:100'],
            'website'        => ['nullable', 'string', 'max:255'],
            'facebook'       => ['nullable', 'string', 'max:255'],
            'twitter'        => ['nullable', 'string', 'max:255'],
            'youtube'        => ['nullable', 'string', 'max:255'],
            'avatar'         => ['nullable', 'image', 'mimes:jpeg,png,jpg,webp,heic,heif', 'max:20480'],
            'avatar_cropped' => ['nullable', 'string'],
        ]);

        $regData = is_array($user->reg_data) ? $user->reg_data : [];

        // 1. Process Cropped Base64 Avatar or Direct File Upload
        $avatarPath = null;
        if ($request->filled('avatar_cropped') && str_starts_with($request->input('avatar_cropped'), 'data:image')) {
            try {
                $avatarPath = \App\Services\ImageOptimizerService::convertBase64AndStore(
                    $request->input('avatar_cropped'),
                    'authors',
                    'public',
                    85,
                    600,
                    600
                );
            } catch (\Throwable $e) {
                Log::warning("Base64 avatar conversion failed: " . $e->getMessage());
            }
        }

        if (!$avatarPath && $request->hasFile('avatar')) {
            try {
                $avatarPath = \App\Services\ImageOptimizerService::convertAndStore(
                    $request->file('avatar'),
                    'authors',
                    'public',
                    85,
                    600,
                    600
                );
            } catch (\Throwable $e) {
                Log::warning("Direct avatar conversion failed: " . $e->getMessage());
            }
        }

        if ($avatarPath) {
            $user->avatar = $avatarPath;
            $regData['avatar'] = $avatarPath;
        }

        // Update extra reg_data fields (Author Bengali/English names, pen name, genres, bio, nid, addresses, etc.)
        $extraFields = [
            'full_name', 'name_bn', 'name_en', 'name_bangla', 'name_english',
            'pen_name', 'genre', 'genres', 'bio', 'nid', 'dob', 'profession',
            'payout_number', 'present_address', 'permanent_address', 'father_name', 'mother_name',
            'shop_name', 'zone', 'publisher_name', 'address', 'trade_license',
            'website', 'facebook', 'twitter', 'youtube'
        ];

        foreach ($extraFields as $field) {
            if ($request->has($field)) {
                $val = $request->input($field);
                if ($field === 'genre' && is_string($val)) {
                    $regData['genre'] = $val;
                    $regData['genres'] = array_filter(array_map('trim', explode(',', $val)));
                } else {
                    $regData[$field] = $val;
                }
            }
        }

        $wasPending = ($user->reg_status === 'pending');
        $isNowApproved = ($validated['reg_status'] === 'approved');

        // Author Name (English) is primary display name
        $user->name = trim($validated['name']);
        $user->email = $validated['email'];
        $user->phone = $validated['phone'];
        $user->role = $validated['role'];
        $user->reg_type = $validated['role'];
        $user->reg_status = $validated['reg_status'];
        $user->reg_data = $regData;
        
        // Ensure user is only active if approved or explicitly checked when approved
        $user->is_active = ($validated['reg_status'] === 'approved') ? $request->boolean('is_active', true) : false;

        if ($wasPending && $isNowApproved) {
            $user->approved_by = auth()->id();
            $user->approved_at = now();
            $user->rejection_reason = null;
        }

        $user->save();

        // Update authors directory & linked blog posts if role is author
        if ($user->role === 'author' || $user->reg_type === 'author') {
            try {
                $authorName = !empty($regData['pen_name']) ? trim($regData['pen_name']) : (!empty($regData['name_bn']) ? trim($regData['name_bn']) : $user->name);
                $nameBn = !empty($regData['name_bn']) ? trim($regData['name_bn']) : (!empty($regData['name_bangla']) ? trim($regData['name_bangla']) : $authorName);
                $nameEn = $user->name;
                $authorAvatar = $user->avatar ?: ($avatarPath ?: ($regData['avatar'] ?? null));

                $socialLinks = array_filter([
                    'facebook' => $request->input('facebook') ?: ($regData['facebook'] ?? null),
                    'twitter'  => $request->input('twitter') ?: ($regData['twitter'] ?? null),
                    'youtube'  => $request->input('youtube') ?: ($regData['youtube'] ?? null),
                ]);

                $authorRecord = $user->getAuthorRecord();
                if ($authorRecord) {
                    if ($authorAvatar) {
                        $authorRecord->avatar = $authorAvatar;
                    }
                    $authorRecord->name = $authorName;
                    $authorRecord->name_bn = $nameBn;
                    $authorRecord->name_en = $nameEn;
                    $authorRecord->user_id = $user->id;
                    $authorRecord->email = $user->email;
                    $authorRecord->phone = $user->phone;
                    if (isset($regData['bio'])) {
                        $authorRecord->bio = $regData['bio'];
                    }
                    if (isset($regData['website'])) {
                        $authorRecord->website = $regData['website'];
                    }
                    if (!empty($socialLinks)) {
                        $authorRecord->social_links = $socialLinks;
                    }
                    $authorRecord->is_active = $user->is_active;
                    $authorRecord->is_verified = ($user->reg_status === 'approved');
                    $authorRecord->save();
                } else {
                    \Modules\Author\Models\Author::findOrCreateUnified([
                        'name'         => $authorName,
                        'name_en'      => $nameEn,
                        'name_bn'      => $nameBn,
                        'email'        => $user->email,
                        'phone'        => $user->phone,
                        'bio'          => $regData['bio'] ?? null,
                        'avatar'       => $authorAvatar,
                        'website'      => $regData['website'] ?? null,
                        'social_links' => !empty($socialLinks) ? $socialLinks : null,
                        'user_id'      => $user->id,
                        'is_active'    => $user->is_active,
                        'is_verified'  => ($user->reg_status === 'approved'),
                    ]);
                }

                // Clear author caches
                try {
                    \Illuminate\Support\Facades\Cache::forget('authors_directory_all');
                    \Illuminate\Support\Facades\Cache::forget('featured_authors_home');
                } catch (\Throwable $e) {}

                // Sync blog posts owner_name
                \Modules\Blog\Models\BlogPost::where('author_id', $user->id)
                    ->orWhere('submitted_by', $user->id)
                    ->update(['owner_name' => $authorName]);
            } catch (\Throwable $e) {
                Log::warning("Could not sync updated author directory: " . $e->getMessage());
            }
        }

        // Send approval email if newly approved
        if ($wasPending && $isNowApproved && $user->email && !str_ends_with($user->email, '@buyer.ideaabd.com')) {
            try {
                Mail::to($user->email)->send(new UserApprovedMail($user));
            } catch (\Throwable $e) {
                Log::warning("Could not send user approval email on edit: " . $e->getMessage());
            }
        }

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'রেজিস্ট্রেশনের তথ্য সফলভাবে আপডেট করা হয়েছে।',
                'user'    => $user,
            ]);
        }

        return redirect()->route('admin.registrations.show', $user)
            ->with('success', 'রেজিস্ট্রেশনের তথ্য সফলভাবে আপডেট করা হয়েছে।');
    }

    // Quick Update via AJAX modal
    public function quickUpdate(Request $request, User $user)
    {
        return $this->update($request, $user);
    }

    // Approve Registration
    public function approve(Request $request, User $user)
    {
        $user->update([
            'reg_status'       => User::STATUS_APPROVED,
            'is_active'        => true,
            'approved_by'      => auth()->id(),
            'approved_at'      => now(),
            'rejection_reason' => null,
            'email_verified_at'=> $user->email_verified_at ?: now(),
        ]);

        // If user is author, activate & sync their entry in authors table using unified resolution
        if ($user->role === 'author' || $user->reg_type === 'author') {
            try {
                $regData = is_array($user->reg_data) ? $user->reg_data : [];
                $authorName = !empty($regData['pen_name']) ? trim($regData['pen_name']) : (!empty($regData['name_bn']) ? trim($regData['name_bn']) : $user->name);
                $nameBn = !empty($regData['name_bn']) ? trim($regData['name_bn']) : (!empty($regData['name_bangla']) ? trim($regData['name_bangla']) : $authorName);
                $nameEn = $user->name;
                $authorAvatar = $user->avatar ?: ($regData['avatar'] ?? null);

                $socialLinks = array_filter([
                    'facebook' => $regData['facebook'] ?? null,
                    'twitter'  => $regData['twitter'] ?? null,
                    'youtube'  => $regData['youtube'] ?? null,
                ]);

                $authorRecord = $user->getAuthorRecord();
                if ($authorRecord) {
                    $authorRecord->name = $authorName;
                    $authorRecord->name_bn = $nameBn;
                    $authorRecord->name_en = $nameEn;
                    $authorRecord->user_id = $user->id;
                    if (!empty($regData['bio'])) {
                        $authorRecord->bio = $regData['bio'];
                    }
                    if (!empty($authorAvatar)) {
                        $authorRecord->avatar = $authorAvatar;
                    }
                    if (!empty($regData['website'])) {
                        $authorRecord->website = $regData['website'];
                    }
                    if (!empty($socialLinks)) {
                        $authorRecord->social_links = $socialLinks;
                    }
                    $authorRecord->is_active = true;
                    $authorRecord->is_verified = true;
                    $authorRecord->save();
                } else {
                    \Modules\Author\Models\Author::findOrCreateUnified([
                        'name'         => $authorName,
                        'name_en'      => $nameEn,
                        'name_bn'      => $nameBn,
                        'email'        => $user->email,
                        'phone'        => $user->phone,
                        'bio'          => $regData['bio'] ?? null,
                        'avatar'       => $authorAvatar,
                        'website'      => $regData['website'] ?? null,
                        'social_links' => !empty($socialLinks) ? $socialLinks : null,
                        'user_id'      => $user->id,
                        'is_active'    => true,
                        'is_verified'  => true,
                    ]);
                }

                // Clear author caches
                try {
                    \Illuminate\Support\Facades\Cache::forget('authors_directory_all');
                    \Illuminate\Support\Facades\Cache::forget('featured_authors_home');
                } catch (\Throwable $e) {}

                // Sync blog posts owner_name
                \Modules\Blog\Models\BlogPost::where('author_id', $user->id)
                    ->orWhere('submitted_by', $user->id)
                    ->update(['owner_name' => $authorName]);
            } catch (\Throwable $e) {
                Log::warning("Could not sync author entry on approval: " . $e->getMessage());
            }
        }

        // If user is publisher, sync/activate publisher record
        if ($user->role === 'publisher') {
            try {
                $user->getPublisherRecord();
            } catch (\Throwable $e) {
                Log::warning("Could not sync publisher entry on approval: " . $e->getMessage());
            }
        }

        // Send approval notification email
        if ($user->email && !str_ends_with($user->email, '@buyer.ideaabd.com')) {
            try {
                Mail::to($user->email)->send(new UserApprovedMail($user));
            } catch (\Throwable $e) {
                Log::warning("Could not send user approval email: " . $e->getMessage());
            }
        }

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success'    => true,
                'message'    => "{$user->name} এর রেজিস্ট্রেশন অনুমোদন করা হয়েছে এবং অ্যাকাউন্টটি সক্রিয় করা হয়েছে।",
                'reg_status' => 'approved',
                'is_active'  => true,
                'user'       => $user,
            ]);
        }

        return redirect()->route('admin.registrations.index')
            ->with('success', "{$user->name} এর রেজিস্ট্রেশন অনুমোদন করা হয়েছে এবং অ্যাকাউন্টটি সক্রিয় করা হয়েছে।");
    }

    // Reject Registration
    public function reject(Request $request, User $user)
    {
        $request->validate(['reason' => 'required|string|max:500']);

        $user->update([
            'reg_status'       => User::STATUS_REJECTED,
            'is_active'        => false,
            'rejection_reason' => $request->reason,
        ]);

        // Deactivate linked directory entries
        if ($user->role === 'author') {
            try {
                DB::table('authors')->where('email', $user->email)->update(['is_active' => false]);
            } catch (\Throwable $e) {
                Log::warning("Could not deactivate author on reject: " . $e->getMessage());
            }
        }
        if ($user->role === 'publisher') {
            try {
                DB::table('publishers')->where('email', $user->email)->update(['is_active' => false]);
            } catch (\Throwable $e) {
                Log::warning("Could not deactivate publisher on reject: " . $e->getMessage());
            }
        }

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success'    => true,
                'message'    => "{$user->name} এর রেজিস্ট্রেশন বাতিল করা হয়েছে।",
                'reg_status' => 'rejected',
                'is_active'  => false,
                'reason'     => $request->reason,
                'user'       => $user,
            ]);
        }

        return redirect()->route('admin.registrations.index')
            ->with('success', "{$user->name} এর রেজিস্ট্রেশন বাতিল করা হয়েছে।");
    }

    // Toggle Active/Inactive Status
    public function toggleStatus(Request $request, User $user)
    {
        $user->is_active = !$user->is_active;
        $user->save();

        if ($user->role === 'author') {
            try {
                DB::table('authors')->where('email', $user->email)->update(['is_active' => $user->is_active]);
            } catch (\Throwable $e) {}
        }
        if ($user->role === 'publisher') {
            try {
                DB::table('publishers')->where('email', $user->email)->update(['is_active' => $user->is_active]);
            } catch (\Throwable $e) {}
        }

        $statusText = $user->is_active ? 'সক্রিয়' : 'নিষ্ক্রিয়';

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success'   => true,
                'is_active' => $user->is_active,
                'message'   => "{$user->name} এর অ্যাকাউন্ট {$statusText} করা হয়েছে।",
            ]);
        }

        return back()->with('success', "{$user->name} এর অ্যাকাউন্ট {$statusText} করা হয়েছে।");
    }

    // Sync author profile directly to Authors Directory
    public function syncAuthor(Request $request, User $user)
    {
        try {
            $regData = is_array($user->reg_data) ? $user->reg_data : [];
            $authorName = $regData['pen_name'] ?? ($regData['name_bangla'] ?? ($regData['full_name'] ?? $user->name));
            $authorAvatar = $user->avatar ?: ($regData['avatar'] ?? null);

            $author = $user->getAuthorRecord();
            if (!$author) {
                $author = \Modules\Author\Models\Author::findOrCreateUnified([
                    'user_id'     => $user->id,
                    'name'        => $authorName,
                    'name_bn'     => $regData['name_bn'] ?? ($regData['name_bangla'] ?? null),
                    'name_en'     => $user->name,
                    'email'       => $user->email,
                    'phone'       => $user->phone,
                    'bio'         => $regData['bio'] ?? null,
                    'website'     => $regData['website'] ?? null,
                    'avatar'      => $authorAvatar,
                    'is_active'   => true,
                    'is_verified' => true,
                ]);
            } else {
                $author->name = $authorName;
                $author->name_en = $user->name;
                if (!empty($regData['name_bn']) || !empty($regData['name_bangla'])) {
                    $author->name_bn = $regData['name_bn'] ?? $regData['name_bangla'];
                }
                if (!empty($regData['bio'])) {
                    $author->bio = $regData['bio'];
                }
                if (!empty($authorAvatar)) {
                    $author->avatar = $authorAvatar;
                }
                if (!empty($user->email)) {
                    $author->email = $user->email;
                }
                if (!empty($user->phone)) {
                    $author->phone = $user->phone;
                }
                if (!empty($regData['website'])) {
                    $author->website = $regData['website'];
                }
                if (!empty($regData['payout_method'])) {
                    $author->payout_account_type = $regData['payout_method'];
                }
                if (!empty($regData['payout_number'])) {
                    $author->payout_account_details = $regData['payout_number'];
                }
                $author->is_active = true;
                $author->is_verified = true;
                $author->user_id = $user->id;
                $author->save();
            }

            // Mark status as synced
            $regData['profile_update_status'] = 'synced';
            $regData['last_synced_at'] = now()->toDateTimeString();
            $user->reg_data = $regData;
            $user->save();

            // Clear author cache
            try {
                \Illuminate\Support\Facades\Cache::forget('authors_directory_all');
                \Illuminate\Support\Facades\Cache::forget('featured_authors_home');
            } catch (\Throwable $e) {}

            $authorUrl = route('authors.show', $author->slug ?: $author->id);

            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'success'    => true,
                    'message'    => 'লেখক প্রোফাইল সফলভাবে লেখক ডিরেক্টরিতে সিঙ্ক ও লাইভ করা হয়েছে!',
                    'author'     => $author,
                    'author_url' => $authorUrl,
                ]);
            }

            return back()->with('success', 'লেখক প্রোফাইল সফলভাবে লেখক ডিরেক্টরিতে সিঙ্ক ও লাইভ করা হয়েছে!');
        } catch (\Throwable $e) {
            \Log::error('Author Sync Failed: ' . $e->getMessage());
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'সিঙ্ক করতে ত্রুটি হয়েছে: ' . $e->getMessage(),
                ], 500);
            }
            return back()->with('error', 'সিঙ্ক করতে ত্রুটি হয়েছে: ' . $e->getMessage());
        }
    }

    // Cancel / Delete registration entirely
    public function cancel(Request $request, User $user)
    {
        abort_unless(in_array($user->role, ['seller', 'publisher', 'author', 'buyer']), 404);
        $name = $user->name;

        // If author, clean up entry in authors directory
        if ($user->role === 'author') {
            try {
                DB::table('authors')->where('email', $user->email)->orWhere('phone', $user->phone)->delete();
            } catch (\Throwable $e) {
                Log::warning("Could not clean up author directory on delete: " . $e->getMessage());
            }
        }

        $user->forceDelete();

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => "{$name} এর রেজিস্ট্রেশন আবেদন ও অ্যাকাউন্টটি সম্পূর্ণ মুছে ফেলা হয়েছে।",
            ]);
        }

        return redirect()->route('admin.registrations.index')
            ->with('success', "{$name} এর রেজিস্ট্রেশন আবেদন ও অ্যাকাউন্টটি সম্পূর্ণ মুছে ফেলা হয়েছে।");
    }
}
