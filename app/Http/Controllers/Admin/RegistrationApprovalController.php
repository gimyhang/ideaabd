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
        abort_unless(in_array($user->role, ['seller', 'publisher', 'author', 'buyer']), 404);
        return view('admin.registrations.show', compact('user'));
    }

    // Edit individual registration detail page
    public function edit(User $user)
    {
        abort_unless(in_array($user->role, ['seller', 'publisher', 'author', 'buyer']), 404);
        return view('admin.registrations.edit', compact('user'));
    }

    // AJAX Details endpoint for popup modal preview
    public function details(User $user)
    {
        abort_unless(in_array($user->role, ['seller', 'publisher', 'author', 'buyer']), 404);

        $regData = is_array($user->reg_data) ? $user->reg_data : [];

        $avatarUrl = null;
        if (!empty($user->avatar)) {
            $avatarUrl = str_starts_with($user->avatar, 'http') ? $user->avatar : asset('storage/' . ltrim($user->avatar, '/'));
        }

        return response()->json([
            'success'               => true,
            'user'                  => $user,
            'avatar_url'            => $avatarUrl,
            'reg_data'              => $regData,
            'created_at_formatted'  => $user->created_at ? $user->created_at->format('d M Y, h:i A') : '',
            'approved_at_formatted' => $user->approved_at ? \Carbon\Carbon::parse($user->approved_at)->format('d M Y, h:i A') : null,
        ]);
    }

    // Update registration detail
    public function update(Request $request, User $user)
    {
        abort_unless(in_array($user->role, ['seller', 'publisher', 'author', 'buyer']), 404);

        $validated = $request->validate([
            'name'           => ['required', 'string', 'max:255'],
            'email'          => ['required', 'email', 'max:255', 'unique:users,email,' . $user->id],
            'phone'          => ['required', 'string', 'max:20', 'unique:users,phone,' . $user->id],
            'role'           => ['required', 'in:author,seller,publisher,buyer'],
            'reg_status'     => ['required', 'in:pending,approved,rejected'],
            'is_active'      => ['nullable', 'boolean'],
            'pen_name'       => ['nullable', 'string', 'max:255'],
            'genre'          => ['nullable', 'string', 'max:255'],
            'bio'            => ['nullable', 'string'],
            'nid'            => ['nullable', 'string', 'max:50'],
            'shop_name'      => ['nullable', 'string', 'max:255'],
            'publisher_name' => ['nullable', 'string', 'max:255'],
            'address'        => ['nullable', 'string'],
            'trade_license'  => ['nullable', 'string', 'max:100'],
        ]);

        $regData = is_array($user->reg_data) ? $user->reg_data : [];

        // Update extra reg_data fields
        foreach (['pen_name', 'genre', 'bio', 'nid', 'shop_name', 'publisher_name', 'address', 'trade_license'] as $field) {
            if ($request->has($field)) {
                $regData[$field] = $request->input($field);
            }
        }

        $wasPending = ($user->reg_status === 'pending');
        $isNowApproved = ($validated['reg_status'] === 'approved');

        $user->name = $validated['name'];
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

        // Update authors table if role is author
        if ($user->role === 'author') {
            try {
                $authorName = !empty($regData['pen_name']) ? $regData['pen_name'] : $user->name;
                \Modules\Author\Models\Author::findOrCreateUnified([
                    'name'        => $authorName,
                    'email'       => $user->email,
                    'phone'       => $user->phone,
                    'bio'         => $regData['bio'] ?? null,
                    'is_active'   => $user->is_active,
                    'is_verified' => ($user->reg_status === 'approved'),
                ]);
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

        // If user is author, activate their entry in authors table using unified resolution
        if ($user->role === 'author') {
            try {
                $regData = is_array($user->reg_data) ? $user->reg_data : [];
                $authorName = !empty($regData['pen_name']) ? $regData['pen_name'] : $user->name;

                \Modules\Author\Models\Author::findOrCreateUnified([
                    'name'        => $authorName,
                    'email'       => $user->email,
                    'phone'       => $user->phone,
                    'bio'         => $regData['bio'] ?? null,
                    'is_active'   => true,
                    'is_verified' => true,
                ]);
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

        return back()->with('success', "{$user->name} এর রেজিস্ট্রেশন অনুমোদন করা হয়েছে এবং ইমেইলে নোটিফিকেশন পাঠানো হয়েছে।");
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

        return back()->with('success', "{$user->name} এর রেজিস্ট্রেশন বাতিল করা হয়েছে।");
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
