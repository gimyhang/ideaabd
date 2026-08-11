<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

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

    // Approve
    public function approve(User $user)
    {
        $user->update([
            'reg_status'  => User::STATUS_APPROVED,
            'is_active'   => true,
            'approved_by' => auth()->id(),
            'approved_at' => now(),
            'rejection_reason' => null,
        ]);

        return back()->with('success', "{$user->name} এর রেজিস্ট্রেশন অনুমোদন করা হয়েছে।");
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
        $user->delete();
        return redirect()->route('admin.registrations.index')
            ->with('success', "রেজিস্ট্রেশন মুছে ফেলা হয়েছে।");
    }
}
