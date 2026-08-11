<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Bill;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\Rule;

/**
 * Sub-admin (and seller) accounts, managed from under the site admin dashboard.
 */
class SubAdminController extends Controller
{
    private const MANAGED_ROLES = [User::ROLE_SUB_ADMIN, User::ROLE_SELLER];

    public function index(Request $request): View
    {
        $staff = User::query()
            ->whereIn('role', self::MANAGED_ROLES)
            ->when($request->filled('search'), function ($q) use ($request) {
                $term = '%' . $request->string('search')->trim() . '%';
                $q->where(fn ($w) => $w->where('name', 'like', $term)
                    ->orWhere('email', 'like', $term)
                    ->orWhere('phone', 'like', $term));
            })
            ->when($request->filled('role'), fn ($q) => $q->where('role', $request->string('role')))
            ->latest()
            ->paginate(20)
            ->withQueryString();

        // Billed revenue per staff member, so the list is useful at a glance.
        $revenue = collect();
        if ($staff->isNotEmpty() && Schema::hasTable('bills')) {
            $revenue = Bill::whereIn('seller_id', $staff->pluck('id'))
                ->select('seller_id', DB::raw('SUM(total) as revenue'), DB::raw('COUNT(*) as bills'))
                ->groupBy('seller_id')
                ->get()
                ->keyBy('seller_id');
        }

        return view('admin.sub-admins.index', [
            'staff'   => $staff,
            'revenue' => $revenue,
            'counts'  => [
                'sub_admin' => User::where('role', User::ROLE_SUB_ADMIN)->count(),
                'seller'    => User::where('role', User::ROLE_SELLER)->count(),
            ],
        ]);
    }

    public function create(): View
    {
        return view('admin.sub-admins.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name'     => ['required', 'string', 'max:255'],
            'email'    => ['required', 'email', 'max:255', 'unique:users,email'],
            'phone'    => ['nullable', 'string', 'max:30'],
            'role'     => ['required', Rule::in(self::MANAGED_ROLES)],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ], [], [
            'name'     => 'নাম',
            'email'    => 'ইমেইল',
            'phone'    => 'ফোন',
            'role'     => 'ভূমিকা',
            'password' => 'পাসওয়ার্ড',
        ]);

        $user = User::create([
            ...$data,
            'is_active'  => true,
            'reg_status' => User::STATUS_APPROVED,
            'reg_type'   => $data['role'],
        ]);

        return redirect()
            ->route('admin.sub-admins.index')
            ->with('success', "{$user->name} — অ্যাকাউন্ট তৈরি হয়েছে।");
    }

    public function show(User $user): View
    {
        abort_unless(in_array($user->role, self::MANAGED_ROLES, true), 404);

        $bills = Schema::hasTable('bills')
            ? Bill::where('seller_id', $user->id)->latest()->limit(20)->get()
            : collect();

        return view('admin.sub-admins.show', [
            'staff' => $user,
            'bills' => $bills,
            'totals' => [
                'bills'   => $bills->count(),
                'revenue' => (float) $bills->sum('total'),
            ],
        ]);
    }

    /** Enable/disable an account without deleting its billing history. */
    public function toggle(User $user): RedirectResponse
    {
        abort_unless(in_array($user->role, self::MANAGED_ROLES, true), 404);

        $user->update(['is_active' => ! $user->is_active]);

        return back()->with('success', $user->is_active
            ? "{$user->name} — অ্যাকাউন্ট সক্রিয় করা হয়েছে।"
            : "{$user->name} — অ্যাকাউন্ট নিষ্ক্রিয় করা হয়েছে।");
    }

    public function destroy(User $user): RedirectResponse
    {
        abort_unless(in_array($user->role, self::MANAGED_ROLES, true), 404);

        $name = $user->name;
        $user->delete();   // soft delete — bills stay intact

        return redirect()
            ->route('admin.sub-admins.index')
            ->with('success', "{$name} — অ্যাকাউন্ট সরানো হয়েছে।");
    }
}
