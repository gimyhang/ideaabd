<?php

namespace App\Services;

use App\Models\AdminActivityLog;
use App\Models\AdminPermission;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

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
     * Check if a given role has a permission key.
     */
    public function hasPermission(string $role, string $permissionKey): bool
    {
        if ($role === User::ROLE_ADMIN) {
            return true; // Admin has all permissions
        }

        try {
            if (! Schema::hasTable('admin_permissions') || ! Schema::hasTable('role_has_permissions')) {
                return true; // Fallback gracefully
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
     * Get recent activity logs for dashboard feed.
     */
    public function recentLogs(int $limit = 10)
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
}
