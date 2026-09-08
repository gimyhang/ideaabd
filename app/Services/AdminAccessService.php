<?php

namespace App\Services;

use App\Models\AdminActivityLog;
use App\Models\AdminPermission;
use App\Models\AdminRole;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class AdminAccessService
{
    /**
     * Safely log an admin action into admin_activity_logs.
     */
    public function log(string $actionType, string $description, ?string $targetType = null, ?int $targetId = null): void
    {
        try {
            if (! Schema::hasTable('admin_activity_logs')) {
                return;
            }

            AdminActivityLog::create([
                'user_id'     => auth()->id(),
                'action_type' => $actionType,
                'description' => $description,
                'target_type' => $targetType,
                'target_id'   => $targetId,
                'ip_address'  => request()->ip(),
                'user_agent'  => request()->userAgent(),
            ]);
        } catch (\Throwable $e) {
            report($e);
        }
    }

    /**
     * Get summary KPI statistics for Enterprise IAM Dashboard.
     */
    public function getIamSummaryStats(): array
    {
        $totalRoles = Schema::hasTable('admin_roles') ? AdminRole::where('is_active', true)->count() : 4;
        $customRoles = Schema::hasTable('admin_roles') ? AdminRole::where('is_system', false)->count() : 0;
        $totalPermissions = Schema::hasTable('admin_permissions') ? AdminPermission::count() : 0;
        
        $managedRoles = [User::ROLE_SUB_ADMIN, User::ROLE_SELLER, 'operations_manager', 'chief_editor', 'digital_marketer', 'tech_admin', 'finance_controller', 'support_agent'];
        
        $subordinateStaffQuery = User::where(function ($q) use ($managedRoles) {
            $q->whereIn('role', $managedRoles)
              ->orWhereNotNull('custom_role_id');
        })->where('role', '!=', User::ROLE_ADMIN);

        $totalStaff = (clone $subordinateStaffQuery)->count();
        $activeStaff = (clone $subordinateStaffQuery)->where('is_active', true)->count();
        $suspendedStaff = (clone $subordinateStaffQuery)->where('is_active', false)->count();

        $directOverridesCount = 0;
        if (Schema::hasTable('user_has_permissions')) {
            $directOverridesCount = DB::table('user_has_permissions')->distinct('user_id')->count('user_id');
        }

        return [
            'total_roles'         => $totalRoles,
            'custom_roles'        => $customRoles,
            'total_permissions'   => $totalPermissions,
            'total_staff'         => $totalStaff,
            'active_staff'        => $activeStaff,
            'suspended_staff'     => $suspendedStaff,
            'direct_overrides'    => $directOverridesCount,
        ];
    }

    /**
     * Get all active roles with assigned users count.
     */
    public function getAllRoles()
    {
        if (! Schema::hasTable('admin_roles')) {
            return collect();
        }

        return AdminRole::orderBy('is_system', 'desc')->orderBy('name')->get();
    }

    /**
     * Get all permissions grouped by module.
     */
    public function getPermissionsGrouped()
    {
        if (! Schema::hasTable('admin_permissions')) {
            return collect();
        }

        return AdminPermission::all()->groupBy('module');
    }

    /**
     * Get map of permissions per role slug: ['admin' => [1,2,3], 'sub_admin' => [...]]
     */
    public function getRolePermissionsMap(): array
    {
        $map = [];
        if (! Schema::hasTable('role_has_permissions')) {
            return $map;
        }

        $rows = DB::table('role_has_permissions')->get();
        foreach ($rows as $row) {
            $map[$row->role][] = (int) $row->permission_id;
        }

        return $map;
    }

    /**
     * Sync entire permissions matrix across multiple roles.
     */
    public function syncMatrix(array $permissionsByRole): void
    {
        if (! Schema::hasTable('role_has_permissions')) {
            return;
        }

        DB::transaction(function () use ($permissionsByRole) {
            // Keep admin role permissions intact
            $allPermIds = DB::table('admin_permissions')->pluck('id')->toArray();
            
            DB::table('role_has_permissions')->truncate();

            // Always grant all to admin
            foreach ($allPermIds as $pId) {
                DB::table('role_has_permissions')->insert([
                    'role'          => 'admin',
                    'permission_id' => $pId,
                    'created_at'    => now(),
                    'updated_at'    => now(),
                ]);
            }

            foreach ($permissionsByRole as $roleSlug => $permIds) {
                if ($roleSlug === 'admin') {
                    continue; // Already assigned all
                }

                foreach ((array) $permIds as $pId) {
                    if ((int) $pId > 0) {
                        DB::table('role_has_permissions')->insert([
                            'role'          => (string) $roleSlug,
                            'permission_id' => (int) $pId,
                            'created_at'    => now(),
                            'updated_at'    => now(),
                        ]);
                    }
                }
            }
        });

        $this->log('update_permissions_matrix', 'অ্যাডমিন সম্পূর্ণ রোল ও পারমিশন ম্যাট্রিক্স আপডেট করেছেন');
    }

    /**
     * Create a new custom role.
     */
    public function createRole(array $data): AdminRole
    {
        $slug = ! empty($data['slug']) ? Str::slug($data['slug'], '_') : Str::slug($data['name'], '_') . '_' . rand(100, 999);

        $role = AdminRole::create([
            'slug'        => $slug,
            'name'        => trim($data['name']),
            'description' => $data['description'] ?? null,
            'department'  => $data['department'] ?? 'Operations & Support',
            'badge_color' => $data['badge_color'] ?? '#2563eb',
            'icon'        => $data['icon'] ?? 'fas fa-user-shield',
            'is_system'   => false,
            'is_active'   => true,
        ]);

        if (! empty($data['permissions'])) {
            foreach ($data['permissions'] as $pId) {
                DB::table('role_has_permissions')->insert([
                    'role'          => $role->slug,
                    'permission_id' => (int) $pId,
                    'created_at'    => now(),
                    'updated_at'    => now(),
                ]);
            }
        }

        $this->log('create_role', "নতুন কাস্টম রোল তৈরি করা হয়েছে: {$role->name} ({$role->slug})", 'AdminRole', $role->id);

        return $role;
    }

    /**
     * Clone an existing role.
     */
    public function cloneRole(int $roleId, string $newName): AdminRole
    {
        $source = AdminRole::findOrFail($roleId);
        $newSlug = Str::slug($newName, '_') . '_clone_' . rand(10, 99);

        $cloned = AdminRole::create([
            'slug'        => $newSlug,
            'name'        => $newName,
            'description' => "Cloned from {$source->name}. " . ($source->description ?? ''),
            'department'  => $source->department,
            'badge_color' => $source->badge_color,
            'icon'        => $source->icon,
            'is_system'   => false,
            'is_active'   => true,
        ]);

        $sourcePermIds = DB::table('role_has_permissions')->where('role', $source->slug)->pluck('permission_id');
        foreach ($sourcePermIds as $pId) {
            DB::table('role_has_permissions')->insert([
                'role'          => $cloned->slug,
                'permission_id' => (int) $pId,
                'created_at'    => now(),
                'updated_at'    => now(),
            ]);
        }

        $this->log('clone_role', "রোল ক্লোন করা হয়েছে: {$source->name} -> {$cloned->name}", 'AdminRole', $cloned->id);

        return $cloned;
    }

    /**
     * Update custom role metadata.
     */
    public function updateRole(int $roleId, array $data): AdminRole
    {
        $role = AdminRole::findOrFail($roleId);

        $role->update([
            'name'        => trim($data['name']),
            'description' => $data['description'] ?? $role->description,
            'department'  => $data['department'] ?? $role->department,
            'badge_color' => $data['badge_color'] ?? $role->badge_color,
            'icon'        => $data['icon'] ?? $role->icon,
            'is_active'   => isset($data['is_active']) ? (bool) $data['is_active'] : $role->is_active,
        ]);

        $this->log('update_role', "রোল তথ্য আপডেট করা হয়েছে: {$role->name}", 'AdminRole', $role->id);

        return $role;
    }

    /**
     * Delete custom role (non-system).
     */
    public function deleteRole(int $roleId): bool
    {
        $role = AdminRole::findOrFail($roleId);

        if ($role->is_system) {
            throw new \InvalidArgumentException('সিস্টেম প্রি-বিল্ট কোর রোল মুছে ফেলা সম্ভব নয়।');
        }

        // Reassign users to sub_admin before deleting
        User::where('custom_role_id', $role->id)->update([
            'custom_role_id' => null,
            'role'           => User::ROLE_SUB_ADMIN,
        ]);

        DB::table('role_has_permissions')->where('role', $role->slug)->delete();
        $name = $role->name;
        $role->delete();

        $this->log('delete_role', "কাস্টম রোল মুছে ফেলা হয়েছে: {$name}");

        return true;
    }

    /**
     * Get staff users under admin supervision.
     */
    public function getStaffUsers(Request $request)
    {
        $query = User::with(['customRole', 'directPermissions'])
            ->where(function ($q) {
                $q->whereIn('role', [
                    User::ROLE_SUB_ADMIN,
                    User::ROLE_SELLER,
                    'operations_manager',
                    'chief_editor',
                    'digital_marketer',
                    'tech_admin',
                    'finance_controller',
                    'support_agent',
                ])->orWhereNotNull('custom_role_id');
            })
            ->where('role', '!=', User::ROLE_ADMIN);

        if ($request->filled('search')) {
            $term = '%' . trim($request->string('search')) . '%';
            $query->where(function ($w) use ($term) {
                $w->where('name', 'like', $term)
                  ->orWhere('email', 'like', $term)
                  ->orWhere('phone', 'like', $term);
            });
        }

        if ($request->filled('role')) {
            $roleFilter = $request->string('role');
            $query->where(function ($w) use ($roleFilter) {
                $w->where('role', $roleFilter)
                  ->orWhereHas('customRole', fn ($cr) => $cr->where('slug', $roleFilter));
            });
        }

        if ($request->filled('status')) {
            $status = $request->string('status');
            if ($status === 'active') {
                $query->where('is_active', true);
            } elseif ($status === 'inactive') {
                $query->where('is_active', false);
            }
        }

        return $query->latest()->paginate(20)->withQueryString();
    }

    /**
     * Get effective permissions inspector for a specific user.
     */
    public function getUserPermissionInspector(User $user): array
    {
        $roleSlug = $user->customRole?->slug ?? $user->role;
        $rolePermissions = DB::table('role_has_permissions')
            ->where('role', $roleSlug)
            ->pluck('permission_id')
            ->toArray();

        $directRows = DB::table('user_has_permissions')
            ->where('user_id', $user->id)
            ->get();

        $directGrants = [];
        $directDenies = [];
        foreach ($directRows as $row) {
            if ($row->is_granted) {
                $directGrants[] = (int) $row->permission_id;
            } else {
                $directDenies[] = (int) $row->permission_id;
            }
        }

        $allPermissions = AdminPermission::all();
        $inspector = [];

        foreach ($allPermissions as $perm) {
            $inherited = in_array($perm->id, $rolePermissions);
            $overrideGranted = in_array($perm->id, $directGrants);
            $overrideDenied = in_array($perm->id, $directDenies);

            $effective = false;
            $status = 'none';

            if ($overrideGranted) {
                $effective = true;
                $status = 'direct_grant';
            } elseif ($overrideDenied) {
                $effective = false;
                $status = 'direct_deny';
            } elseif ($inherited) {
                $effective = true;
                $status = 'role_inherited';
            }

            $inspector[$perm->id] = [
                'permission'  => $perm,
                'inherited'   => $inherited,
                'override'    => $overrideGranted ? 'grant' : ($overrideDenied ? 'deny' : 'none'),
                'effective'   => $effective,
                'status'      => $status,
            ];
        }

        return [
            'user'             => $user,
            'role_slug'        => $roleSlug,
            'role_name'        => $user->getRoleDisplayName(),
            'matrix'           => $inspector,
            'direct_grants'    => $directGrants,
            'direct_denies'    => $directDenies,
            'total_effective'  => count(array_filter($inspector, fn ($i) => $i['effective'])),
        ];
    }

    /**
     * Save user direct permission overrides.
     */
    public function syncUserDirectPermissions(int $userId, array $grants, array $denies): void
    {
        $user = User::findOrFail($userId);

        DB::transaction(function () use ($user, $grants, $denies) {
            DB::table('user_has_permissions')->where('user_id', $user->id)->delete();

            foreach ($grants as $pId) {
                DB::table('user_has_permissions')->insert([
                    'user_id'       => $user->id,
                    'permission_id' => (int) $pId,
                    'is_granted'    => true,
                    'created_at'    => now(),
                    'updated_at'    => now(),
                ]);
            }

            foreach ($denies as $pId) {
                DB::table('user_has_permissions')->insert([
                    'user_id'       => $user->id,
                    'permission_id' => (int) $pId,
                    'is_granted'    => false,
                    'created_at'    => now(),
                    'updated_at'    => now(),
                ]);
            }
        });

        $this->log('update_user_permissions', "কর্মী {$user->name} এর ব্যক্তিগত পারমিশন ওভাররাইড আপডেট করা হয়েছে", 'User', $user->id);
    }

    /**
     * Terminate all active sessions for a staff member (Force Logout).
     */
    public function terminateStaffSessions(int $userId): bool
    {
        $user = User::findOrFail($userId);
        $user->forceFill([
            'remember_token'         => Str::random(60),
            'session_invalidated_at' => now(),
        ])->save();

        $this->log('terminate_sessions', "কর্মী {$user->name} ({$user->email}) এর সকল ডিভাইসের সেশন বাতিল (ফোর্স লগআউট) করা হয়েছে", 'User', $user->id);

        return true;
    }

    /**
     * Toggle staff account active/suspended state.
     */
    public function toggleStaffStatus(int $userId): User
    {
        $user = User::findOrFail($userId);
        $user->is_active = ! $user->is_active;
        $user->save();

        $statusText = $user->is_active ? 'সক্রিয় (Active)' : 'স্থগিত (Suspended)';
        $this->log('toggle_staff_status', "কর্মী {$user->name} এর অ্যাকাউন্ট স্ট্যাটাস পরিবর্তিত: {$statusText}", 'User', $user->id);

        return $user;
    }

    /**
     * Toggle force password reset on next login.
     */
    public function forcePasswordReset(int $userId): User
    {
        $user = User::findOrFail($userId);
        $user->force_password_reset = ! $user->force_password_reset;
        $user->save();

        $msg = $user->force_password_reset ? 'বাধ্যতামূলক পাসওয়ার্ড রিসেট সক্রিয়' : 'পাসওয়ার্ড রিসেট রিকোয়ারমেন্ট প্রত্যাহার';
        $this->log('force_password_reset', "কর্মী {$user->name} এর {$msg}", 'User', $user->id);

        return $user;
    }

    /**
     * Update staff member assigned role and optional IP whitelist.
     */
    public function updateStaffRoleAndSecurity(int $userId, array $data): User
    {
        $user = User::findOrFail($userId);

        $customRoleId = ! empty($data['custom_role_id']) ? (int) $data['custom_role_id'] : null;
        $roleSlug = ! empty($data['role']) ? trim($data['role']) : ($user->role ?: User::ROLE_SUB_ADMIN);

        if ($customRoleId) {
            $customRole = AdminRole::find($customRoleId);
            if ($customRole) {
                $roleSlug = $customRole->slug;
            }
        }

        $user->custom_role_id = $customRoleId;
        $user->role = $roleSlug;
        if (isset($data['ip_whitelist'])) {
            $user->ip_whitelist = trim($data['ip_whitelist']) ?: null;
        }
        $user->save();

        $this->log('update_staff_role', "কর্মী {$user->name} এর রোল ও সিকিউরিটি কনফিগারেশন আপডেট করা হয়েছে: {$user->getRoleDisplayName()}", 'User', $user->id);

        return $user;
    }

    /**
     * Recent activity logs for IAM audit feed.
     */
    public function recentLogs(int $limit = 20)
    {
        try {
            if (! Schema::hasTable('admin_activity_logs')) {
                return collect();
            }

            return AdminActivityLog::with('user')
                ->latest()
                ->limit($limit)
                ->get();
        } catch (\Throwable) {
            return collect();
        }
    }

    /**
     * Check if a given role has a permission key.
     */
    public function hasPermission(string $role, string $permissionKey): bool
    {
        if ($role === User::ROLE_ADMIN) {
            return true;
        }

        try {
            if (! Schema::hasTable('admin_permissions') || ! Schema::hasTable('role_has_permissions')) {
                return true;
            }

            return DB::table('role_has_permissions')
                ->join('admin_permissions', 'role_has_permissions.permission_id', '=', 'admin_permissions.id')
                ->where('role_has_permissions.role', $role)
                ->where('admin_permissions.key', $permissionKey)
                ->exists();
        } catch (\Throwable) {
            return true;
        }
    }

    /**
     * System health check status.
     */
    public function systemHealth(): array
    {
        $dbOk = false;
        try {
            DB::connection()->getPdo();
            $dbOk = true;
        } catch (\Throwable) {
            $dbOk = false;
        }

        $pendingModeration = 0;
        try {
            if (Schema::hasTable('books')) {
                $pendingModeration += DB::table('books')->where('status', 'pending')->count();
            }
            if (Schema::hasTable('ebooks')) {
                $pendingModeration += DB::table('ebooks')->where('status', 'pending')->count();
            }
        } catch (\Throwable) {}

        $activeAdmins = 0;
        try {
            $activeAdmins = User::whereIn('role', [User::ROLE_ADMIN, User::ROLE_SUB_ADMIN])
                ->where('is_active', true)
                ->count();
        } catch (\Throwable) {}

        return [
            'database'           => $dbOk,
            'storage'            => is_writable(storage_path()),
            'pending_moderation' => $pendingModeration,
            'active_admins'      => $activeAdmins,
            'php_version'        => PHP_VERSION,
            'environment'        => app()->environment(),
        ];
    }

    /**
     * Get all assignable roles (System Pre-built + Custom Created) for dropdowns and appointment modals.
     */
    public function getAllAssignableRoles(): array
    {
        $systemRoles = [
            [
                'slug'        => 'admin',
                'name'        => 'সাইট সুপার অ্যাডমিন (Super Admin)',
                'department'  => 'Executive',
                'badge_color' => '#dc2626',
                'icon'        => 'fas fa-crown',
                'is_system'   => true,
            ],
            [
                'slug'        => 'sub_admin',
                'name'        => 'সাব-অ্যাডমিন (Executive Sub Admin)',
                'department'  => 'Operations & Support',
                'badge_color' => '#2563eb',
                'icon'        => 'fas fa-user-shield',
                'is_system'   => true,
            ],
            [
                'slug'        => 'operations_manager',
                'name'        => 'অপারেশন্স ও ফুলফিলমেন্ট ম্যানেজার',
                'department'  => 'Operations & Support',
                'badge_color' => '#ea580c',
                'icon'        => 'fas fa-headset',
                'is_system'   => false,
            ],
            [
                'slug'        => 'chief_editor',
                'name'        => 'প্রধান সম্পাদক ও কনটেন্ট লিড',
                'department'  => 'Content & Editorial',
                'badge_color' => '#ca8a04',
                'icon'        => 'fas fa-feather-pointed',
                'is_system'   => false,
            ],
            [
                'slug'        => 'digital_marketer',
                'name'        => 'ডিজিটাল মার্কেটিং ও গ্রোথ লিড',
                'department'  => 'Digital Marketing',
                'badge_color' => '#0284c7',
                'icon'        => 'fas fa-bullhorn',
                'is_system'   => false,
            ],
            [
                'slug'        => 'tech_admin',
                'name'        => 'টেকনিক্যাল ও আইটি সিস্টেম অ্যাডমিন',
                'department'  => 'Technical & IT',
                'badge_color' => '#16a34a',
                'icon'        => 'fas fa-laptop-code',
                'is_system'   => false,
            ],
            [
                'slug'        => 'finance_controller',
                'name'        => 'হিসাবরক্ষক ও ফিন্যান্স কন্ট্রোলার',
                'department'  => 'Operations & Support',
                'badge_color' => '#7c3aed',
                'icon'        => 'fas fa-money-check-dollar',
                'is_system'   => false,
            ],
            [
                'slug'        => 'seller',
                'name'        => 'সেলার ও ডিলার (Seller / POS Operator)',
                'department'  => 'Operations & Support',
                'badge_color' => '#059669',
                'icon'        => 'fas fa-store',
                'is_system'   => true,
            ],
            [
                'slug'        => 'support_agent',
                'name'        => 'কাস্টমার সাপোর্ট ও হেল্পডেস্ক এক্সিকিউটিভ',
                'department'  => 'Operations & Support',
                'badge_color' => '#0891b2',
                'icon'        => 'fas fa-life-ring',
                'is_system'   => false,
            ],
            [
                'slug'        => 'author',
                'name'        => 'লেখক ও গবেষক (Author / Writer)',
                'department'  => 'Content & Editorial',
                'badge_color' => '#d97706',
                'icon'        => 'fas fa-pen-fancy',
                'is_system'   => true,
            ],
            [
                'slug'        => 'publisher',
                'name'        => 'প্রকাশক ও প্রকাশনা সংস্থা (Publisher)',
                'department'  => 'Content & Editorial',
                'badge_color' => '#0d9488',
                'icon'        => 'fas fa-building',
                'is_system'   => true,
            ],
            [
                'slug'        => 'buyer',
                'name'        => 'সাধারণ গ্রাহক / পাঠক (General Buyer)',
                'department'  => 'General Public',
                'badge_color' => '#64748b',
                'icon'        => 'fas fa-bag-shopping',
                'is_system'   => true,
            ],
        ];

        // Also merge any custom roles from admin_roles table
        if (Schema::hasTable('admin_roles')) {
            $customDbRoles = AdminRole::where('is_active', true)->get();
            $knownSlugs = array_column($systemRoles, 'slug');
            foreach ($customDbRoles as $dr) {
                if (! in_array($dr->slug, $knownSlugs, true)) {
                    $systemRoles[] = [
                        'slug'        => $dr->slug,
                        'name'        => $dr->name,
                        'department'  => $dr->department ?? 'General',
                        'badge_color' => $dr->badge_color ?? '#6366f1',
                        'icon'        => $dr->icon ?? 'fas fa-user-tag',
                        'is_system'   => (bool) $dr->is_system,
                        'id'          => $dr->id,
                    ];
                }
            }
        }

        return $systemRoles;
    }

    /**
     * Dynamically assign / appoint any user (registered applicant/buyer/staff) to ANY role.
     */
    public function assignUserRole(int $userId, string $roleSlug, ?int $customRoleId = null, ?string $regStatus = 'approved', ?bool $isActive = true, ?string $notes = null): User
    {
        $user = User::findOrFail($userId);
        $oldRole = $user->getRoleDisplayName();

        if ($customRoleId) {
            $customRole = AdminRole::find($customRoleId);
            if ($customRole) {
                $roleSlug = $customRole->slug;
            }
        } else {
            // Check if slug maps to a custom role in DB
            $dbRole = AdminRole::where('slug', $roleSlug)->first();
            if ($dbRole) {
                $customRoleId = $dbRole->id;
            }
        }

        $user->role = $roleSlug;
        $user->custom_role_id = $customRoleId;
        $user->reg_type = $roleSlug;
        $user->reg_status = $regStatus ?? 'approved';
        $user->is_active = $isActive ?? true;
        $user->approved_by = auth()->id();
        $user->approved_at = now();
        $user->rejection_reason = null;

        if ($notes) {
            $regData = is_array($user->reg_data) ? $user->reg_data : [];
            $regData['appointment_notes'] = $notes;
            $regData['appointed_by'] = auth()->user()?->name ?? 'Admin';
            $regData['appointed_at'] = now()->toDateTimeString();
            $user->reg_data = $regData;
        }

        $user->save();

        // If promoted to author, ensure author profile exists
        if ($roleSlug === 'author') {
            try {
                \Modules\Author\Models\Author::findOrCreateUnified([
                    'name'        => $user->name,
                    'name_en'     => $user->name,
                    'name_bn'     => $user->name,
                    'email'       => $user->email,
                    'phone'       => $user->phone,
                    'user_id'     => $user->id,
                    'is_active'   => true,
                    'is_verified' => true,
                ]);
            } catch (\Throwable $e) {}
        }

        $newRoleName = $user->getRoleDisplayName();
        $this->log('assign_user_role', "ইউজার '{$user->name}' (ID: #{$user->id}) কে '{$oldRole}' থেকে '{$newRoleName}' পদে পদায়ন/নিয়োগ দেওয়া হয়েছে।", 'User', $user->id);

        return $user;
    }

    /**
     * Revoke any administrative/staff position and demote user back to standard buyer.
     */
    public function revokeUserRole(int $userId, ?string $reason = null): User
    {
        $user = User::findOrFail($userId);
        $oldRole = $user->getRoleDisplayName();

        $user->role = 'buyer';
        $user->custom_role_id = null;
        $user->reg_type = 'buyer';
        $user->rejection_reason = $reason;

        $regData = is_array($user->reg_data) ? $user->reg_data : [];
        $regData['revoked_role'] = $oldRole;
        $regData['revoked_by'] = auth()->user()?->name ?? 'Admin';
        $regData['revoked_at'] = now()->toDateTimeString();
        $regData['revocation_reason'] = $reason;
        $user->reg_data = $regData;

        $user->save();

        // Clear any direct permission overrides
        if (Schema::hasTable('user_has_permissions')) {
            DB::table('user_has_permissions')->where('user_id', $user->id)->delete();
        }

        $this->log('revoke_user_role', "ইউজার '{$user->name}' (ID: #{$user->id}) এর '{$oldRole}' পদায়ন বাতিল করে সাধারণ ক্রেতা (Buyer) করা হয়েছে।" . ($reason ? " কারণ: {$reason}" : ''), 'User', $user->id);

        return $user;
    }
}
