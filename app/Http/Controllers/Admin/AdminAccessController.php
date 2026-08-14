<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AdminActivityLog;
use App\Models\AdminDashboardSetting;
use App\Models\AdminPermission;
use App\Models\User;
use App\Services\AdminAccessService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class AdminAccessController extends Controller
{
    public function __construct(private readonly AdminAccessService $accessService)
    {
    }

    /**
     * View and manage role permissions matrix.
     */
    public function rolesPermissions(): View
    {
        $permissions = Schema::hasTable('admin_permissions')
            ? AdminPermission::all()->groupBy('module')
            : collect();

        $rolePermissions = [];
        if (Schema::hasTable('role_has_permissions')) {
            $raw = DB::table('role_has_permissions')->get();
            foreach ($raw as $row) {
                $rolePermissions[$row->role][] = $row->permission_id;
            }
        }

        $roles = [
            'admin'     => 'সাইট অ্যাডমিন (Super Admin)',
            'sub_admin' => 'সাব-অ্যাডমিন (Sub Admin)',
            'manager'   => 'ম্যানেজার (Manager)',
            'seller'    => 'সেলার (Seller)',
        ];

        return view('admin.roles-permissions', compact('permissions', 'rolePermissions', 'roles'));
    }

    /**
     * Update permissions assigned to roles.
     */
    public function updatePermissions(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'permissions' => 'nullable|array',
        ]);

        if (! Schema::hasTable('role_has_permissions')) {
            return back()->with('error', 'পারমিশন টেবিল মাইগ্রেট করা হয়নি।');
        }

        DB::transaction(function () use ($data) {
            DB::table('role_has_permissions')->truncate();

            $permissionsByRole = $data['permissions'] ?? [];
            foreach ($permissionsByRole as $role => $permIds) {
                foreach ((array) $permIds as $permId) {
                    DB::table('role_has_permissions')->insert([
                        'role'          => $role,
                        'permission_id' => (int) $permId,
                        'created_at'    => now(),
                        'updated_at'    => now(),
                    ]);
                }
            }
        });

        $this->accessService->log('update_permissions', 'অ্যাডমিন রোল পারমিশন ম্যাট্রিক্স আপডেট করা হয়েছে');

        return back()->with('success', 'রোল ও পারমিশন সফলভাবে আপডেট করা হয়েছে!');
    }

    /**
     * View audit activity logs.
     */
    public function activityLogs(Request $request): View
    {
        $logs = Schema::hasTable('admin_activity_logs')
            ? AdminActivityLog::with('user')
                ->when($request->filled('search'), function ($q) use ($request) {
                    $term = '%' . $request->string('search')->trim() . '%';
                    $q->where('description', 'like', $term)
                      ->orWhere('action_type', 'like', $term)
                      ->orWhere('ip_address', 'like', $term);
                })
                ->when($request->filled('action'), fn ($q) => $q->where('action_type', $request->string('action')))
                ->latest()
                ->paginate(25)
                ->withQueryString()
            : new \Illuminate\Pagination\LengthAwarePaginator([], 0, 25);

        return view('admin.activity-logs', compact('logs'));
    }

    /**
     * View and update system dashboard settings.
     */
    public function systemSettings(): View
    {
        $noticeSetting = Schema::hasTable('admin_dashboard_settings')
            ? AdminDashboardSetting::where('key', 'system_notice')->first()
            : null;

        $maintSetting = Schema::hasTable('admin_dashboard_settings')
            ? AdminDashboardSetting::where('key', 'maintenance_mode')->first()
            : null;

        return view('admin.system-settings', compact('noticeSetting', 'maintSetting'));
    }

    /**
     * Save dashboard settings.
     */
    public function updateSystemSettings(Request $request): RedirectResponse
    {
        $request->validate([
            'notice_text'   => 'nullable|string|max:500',
            'notice_active' => 'nullable|boolean',
            'notice_type'   => 'required|in:info,warning,success,danger',
            'site_logo'     => 'nullable|image|mimes:jpeg,png,jpg,svg,webp|max:2048',
            'banner_1'      => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
            'banner_2'      => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
        ]);

        if (Schema::hasTable('admin_dashboard_settings')) {
            // Handle logo upload
            if ($request->hasFile('site_logo')) {
                $path = $request->file('site_logo')->store('images/brand', 'public');
                AdminDashboardSetting::updateOrCreate(
                    ['key' => 'site_logo'],
                    [
                        'value' => 'storage/' . $path,
                        'updated_by' => auth()->id(),
                    ]
                );
            }

            // Handle Banner 1 upload
            if ($request->hasFile('banner_1')) {
                $path = $request->file('banner_1')->store('images/banners', 'public');
                AdminDashboardSetting::updateOrCreate(
                    ['key' => 'home_banner_1'],
                    [
                        'value' => 'storage/' . $path,
                        'updated_by' => auth()->id(),
                    ]
                );
            }

            // Handle Banner 2 upload
            if ($request->hasFile('banner_2')) {
                $path = $request->file('banner_2')->store('images/banners', 'public');
                AdminDashboardSetting::updateOrCreate(
                    ['key' => 'home_banner_2'],
                    [
                        'value' => 'storage/' . $path,
                        'updated_by' => auth()->id(),
                    ]
                );
            }

            AdminDashboardSetting::updateOrCreate(
                ['key' => 'system_notice'],
                [
                    'value' => [
                        'text'   => $request->input('notice_text', ''),
                        'active' => $request->boolean('notice_active'),
                        'type'   => $request->input('notice_type', 'info'),
                    ],
                    'updated_by' => auth()->id(),
                ]
            );

            $this->accessService->log('update_settings', 'ড্যাশবোর্ড নোটিশ ব্যানার ও সেটিংস আপডেট করা হয়েছে');
        }

        return back()->with('success', 'সিস্টেম সেটিংস সফলভাবে সংরক্ষিত হয়েছে!');
    }
}
