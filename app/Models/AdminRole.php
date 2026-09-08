<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\DB;

class AdminRole extends Model
{
    use HasFactory;

    protected $table = 'admin_roles';

    protected $fillable = [
        'slug',
        'name',
        'description',
        'department',
        'badge_color',
        'icon',
        'is_system',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_system' => 'boolean',
            'is_active' => 'boolean',
        ];
    }

    /**
     * Role Permissions relationship.
     */
    public function permissions(): BelongsToMany
    {
        return $this->belongsToMany(
            AdminPermission::class,
            'role_has_permissions',
            'role',
            'permission_id',
            'slug',
            'id'
        )->withTimestamps();
    }

    /**
     * Users assigned to this role directly or via custom_role_id.
     */
    public function users(): HasMany
    {
        return $this->hasMany(User::class, 'custom_role_id');
    }

    /**
     * Get array of permission IDs assigned to this role.
     */
    public function getPermissionIds(): array
    {
        return DB::table('role_has_permissions')
            ->where('role', $this->slug)
            ->pluck('permission_id')
            ->map(fn ($id) => (int) $id)
            ->toArray();
    }

    /**
     * Get count of users under this role.
     */
    public function getUsersCount(): int
    {
        return User::where('custom_role_id', $this->id)
            ->orWhere('role', $this->slug)
            ->count();
    }

    /**
     * Generate HTML badge representation for this role.
     */
    public function formattedBadge(): string
    {
        $color = $this->badge_color ?: '#2563eb';
        $icon = $this->icon ?: 'fas fa-user-tag';
        $name = htmlspecialchars($this->name, ENT_QUOTES, 'UTF-8');

        return "<span class=\"badge rounded-pill px-2.5 py-1 fw-semibold text-white shadow-2xs\" style=\"background-color: {$color};\"><i class=\"{$icon} me-1\"></i>{$name}</span>";
    }
}
