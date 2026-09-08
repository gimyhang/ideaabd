<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class AdminPermission extends Model
{
    use HasFactory;

    protected $fillable = [
        'key',
        'name',
        'module',
        'description',
    ];

    /**
     * Module titles in Bangla and icons for UI matrix.
     */
    public const MODULE_CONFIG = [
        'dashboard'  => ['title' => 'ড্যাশবোর্ড ও এক্সিকিউটিভ অ্যানালিটিক্স', 'icon' => 'fas fa-gauge-high', 'color' => '#2563eb'],
        'catalog'    => ['title' => 'ক্যাটালগ ও বই ব্যবস্থাপনা',             'icon' => 'fas fa-book',       'color' => '#0891b2'],
        'sales'      => ['title' => 'অর্ডার, POS ও বিক্রয় হিসাব',          'icon' => 'fas fa-cart-shopping', 'color' => '#16a34a'],
        'accounting' => ['title' => 'হিসাবরক্ষণ, ইনভয়েস ও পে-রোল',         'icon' => 'fas fa-scale-balanced', 'color' => '#7c3aed'],
        'staff'      => ['title' => 'এইচআর, কর্মী ও কারিগর টিম',          'icon' => 'fas fa-users-gear', 'color' => '#ea580c'],
        'editorial'  => ['title' => 'সম্পাদকীয়, ব্লগ ও আইডিয়াপত্র',          'icon' => 'fas fa-feather-pointed', 'color' => '#ca8a04'],
        'marketing'  => ['title' => 'ডিজিটাল মার্কেটিং ও প্রমোশন',         'icon' => 'fas fa-bullhorn',   'color' => '#0284c7'],
        'support'    => ['title' => 'কাস্টমার সাপোর্ট ও CRM হেল্পডেস্ক',   'icon' => 'fas fa-headset',    'color' => '#d97706'],
        'ebooks'     => ['title' => 'ডিজিটাল ই-বুক ও রয়্যালটি',            'icon' => 'fas fa-tablet-screen-button', 'color' => '#4f46e5'],
        'security'   => ['title' => 'IAM, ইউজার ও নিরাপত্তা অ্যাক্সেস',    'icon' => 'fas fa-shield-halved', 'color' => '#dc2626'],
        'system'     => ['title' => 'সিস্টেম সেটিংস ও কনফিগারেশন',        'icon' => 'fas fa-sliders',    'color' => '#475569'],
    ];

    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(
            AdminRole::class,
            'role_has_permissions',
            'permission_id',
            'role',
            'id',
            'slug'
        );
    }

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(
            User::class,
            'user_has_permissions',
            'permission_id',
            'user_id'
        )->withPivot('is_granted')->withTimestamps();
    }

    public function getModuleTitle(): string
    {
        return self::MODULE_CONFIG[$this->module]['title'] ?? ucfirst($this->module);
    }

    public function getModuleIcon(): string
    {
        return self::MODULE_CONFIG[$this->module]['icon'] ?? 'fas fa-folder';
    }

    public function getModuleColor(): string
    {
        return self::MODULE_CONFIG[$this->module]['color'] ?? '#64748b';
    }
}

