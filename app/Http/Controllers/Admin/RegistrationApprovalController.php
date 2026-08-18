<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Mail\UserApprovedMail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class RegistrationApprovalController extends Controller
{
    // List all pending registrations
    public function index(Request $request)
    {
        $query = User::whereIn('role', ['seller', 'publisher', 'author'])
            ->orderByRaw("CASE reg_status WHEN 'pending' THEN 0 WHEN 'approved' THEN 1 ELSE 2 END")
            ->orderByDesc('created_at');

        if ($request->filled('status')) {
            $query->where('reg_status', $request->status);
        }
        if ($request->filled('type')) {
            $query->where('reg_type', $request->type);
        }
        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('name', 'like', '%'.$request->search.'%')
                  ->orWhere('email', 'like', '%'.$request->search.'%')
                  ->orWhere('phone', 'like', '%'.$request->search.'%');
            });
        }

        $registrations = $query->paginate(20)->withQueryString();
        $counts = [
            'all'      => User::whereIn('role', ['seller', 'publisher', 'author'])->count(),
            'pending'  => User::whereIn('role', ['seller', 'publisher', 'author'])->where('reg_status', 'pending')->count(),
            'approved' => User::whereIn('role', ['seller', 'publisher', 'author'])->where('reg_status', 'approved')->count(),
            'rejected' => User::whereIn('role', ['seller', 'publisher', 'author'])->where('reg_status', 'rejected')->count(),
        ];

        return view('admin.registrations.index', compact('registrations', 'counts'));
    }

    // Show individual registration detail
    public function show(User $user)
    {
        abort_unless(in_array($user->role, ['seller', 'publisher', 'author']), 404);
        return view('admin.registrations.show', compact('user'));
    }

    // Edit individual registration detail
    public function edit(User $user)
    {
        abort_unless(in_array($user->role, ['seller', 'publisher', 'author']), 404);
        return view('admin.registrations.edit', compact('user'));
    }

    // Update registration detail
    public function update(Request $request, User $user)
    {
        abort_unless(in_array($user->role, ['seller', 'publisher', 'author']), 404);

        $validated = $request->validate([
            'name'       => ['required', 'string', 'max:255'],
            'email'      => ['required', 'email', 'max:255', 'unique:users,email,' . $user->id],
            'phone'      => ['required', 'string', 'max:20', 'unique:users,phone,' . $user->id],
            'role'       => ['required', 'in:author,seller,publisher'],
            'reg_status' => ['required', 'in:pending,approved,rejected'],
            'pen_name'   => ['nullable', 'string', 'max:255'],
            'genre'      => ['nullable', 'string', 'max:255'],
            'bio'        => ['nullable', 'string'],
            'nid'        => ['nullable', 'string', 'max:50'],
            'shop_name'  => ['nullable', 'string', 'max:255'],
            'publisher_name' => ['nullable', 'string', 'max:255'],
            'address'    => ['nullable', 'string'],
            'trade_license' => ['nullable', 'string', 'max:100'],
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
        $user->is_active = ($validated['reg_status'] === 'approved');

        if ($wasPending && $isNowApproved) {
            $user->approved_by = auth()->id();
            $user->approved_at = now();
            $user->rejection_reason = null;
        }

        $user->save();

        // Update authors table if role is author
        if ($user->role === 'author') {
            try {
                $authorName = $regData['pen_name'] ?: $user->name;
                DB::table('authors')->updateOrInsert(
                    ['email' => $user->email],
                    [
                        'name'        => $authorName,
                        'phone'       => $user->phone,
                        'bio'         => $regData['bio'] ?? null,
                        'is_active'   => ($validated['reg_status'] === 'approved'),
                        'is_verified' => ($validated['reg_status'] === 'approved'),
                        'updated_at'  => now(),
                    ]
                );
            } catch (\Throwable $e) {
                Log::warning("Could not sync updated author directory: " . $e->getMessage());
            }
        }

        // Send approval email if approved
        if ($wasPending && $isNowApproved && $user->email && !str_ends_with($user->email, '@buyer.ideaabd.com')) {
            try {
                Mail::to($user->email)->send(new UserApprovedMail($user));
            } catch (\Throwable $e) {
                Log::warning("Could not send user approval email on edit: " . $e->getMessage());
            }
        }

        return redirect()->route('admin.registrations.show', $user)
            ->with('success', 'রেজিস্ট্রেশনের তথ্য সফলভাবে আপডেট করা হয়েছে।');
    }

    // Approve
    public function approve(User $user)
    {
        $user->update([
            'reg_status'       => User::STATUS_APPROVED,
            'is_active'        => true,
            'approved_by'      => auth()->id(),
            'approved_at'      => now(),
            'rejection_reason' => null,
        ]);

        // If user is author, activate their entry in authors table
        if ($user->role === 'author') {
            try {
                $regData = is_array($user->reg_data) ? $user->reg_data : [];
                $authorName = !empty($regData['pen_name']) ? $regData['pen_name'] : $user->name;
                $authorSlug = \Illuminate\Support\Str::slug($authorName) ?: 'author-' . $user->id;

                DB::table('authors')->updateOrInsert(
                    ['email' => $user->email],
                    [
                        'name'        => $authorName,
                        'slug'        => $authorSlug,
                        'phone'       => $user->phone,
                        'bio'         => $regData['bio'] ?? null,
                        'is_active'   => true,
                        'is_verified' => true,
                        'updated_at'  => now(),
                    ]
                );
            } catch (\Throwable $e) {
                Log::warning("Could not sync author entry on approval: " . $e->getMessage());
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

        return back()->with('success', "{$user->name} এর রেজিস্ট্রেশন অনুমোদন করা হয়েছে এবং ইমেইলে নোটিফিকেশন পাঠানো হয়েছে।");
    }

    // Reject
    public function reject(Request $request, User $user)
    {
        $request->validate(['reason' => 'required|string|max:500']);

        $user->update([
            'reg_status'       => User::STATUS_REJECTED,
            'is_active'        => false,
            'rejection_reason' => $request->reason,
        ]);

        return back()->with('success', "{$user->name} এর রেজিস্ট্রেশন বাতিল করা হয়েছে।");
    }

    // Cancel / delete registration entirely
    public function cancel(User $user)
    {
        abort_unless(in_array($user->role, ['seller', 'publisher', 'author']), 404);
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

        return redirect()->route('admin.registrations.index')
            ->with('success', "{$name} এর রেজিস্ট্রেশন আবেদন ও অ্যাকাউন্টটি সম্পূর্ণ মুছে ফেলা হয়েছে।");
    }
}
