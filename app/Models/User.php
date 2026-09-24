<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable, SoftDeletes;

    // Role constants
    const ROLE_ADMIN      = 'admin';
    const ROLE_SUB_ADMIN  = 'sub_admin';
    const ROLE_SELLER     = 'seller';
    const ROLE_PUBLISHER  = 'publisher';
    const ROLE_AUTHOR     = 'author';
    const ROLE_BUYER      = 'buyer';
    const ROLE_CUSTOMER   = 'customer';

    // Registration status
    const STATUS_PENDING  = 'pending';
    const STATUS_APPROVED = 'approved';
    const STATUS_REJECTED = 'rejected';

    protected $fillable = [
        'name', 'email', 'phone', 'password',
        'role', 'custom_role_id', 'avatar', 'is_active',
        'reg_status', 'reg_type', 'reg_data',
        'approved_by', 'approved_at', 'rejection_reason',
        'loyalty_points', 'affiliate_balance',
        'email_verified_at', 'phone_verified_at',
        'force_password_reset', 'ip_whitelist', 'session_invalidated_at',
    ];

    protected $hidden = ['password', 'remember_token'];

    protected function casts(): array
    {
        return [
            'email_verified_at'      => 'datetime',
            'phone_verified_at'      => 'datetime',
            'password'               => 'hashed',
            'is_active'              => 'boolean',
            'reg_data'               => 'array',
            'loyalty_points'         => 'integer',
            'affiliate_balance'      => 'decimal:2',
            'force_password_reset'   => 'boolean',
            'session_invalidated_at' => 'datetime',
        ];
    }

    // ─── Role & Permission IAM helpers ───────────────────────────────
    public function customRole()
    {
        return $this->belongsTo(AdminRole::class, 'custom_role_id');
    }

    public function directPermissions()
    {
        return $this->belongsToMany(
            AdminPermission::class,
            'user_has_permissions',
            'user_id',
            'permission_id'
        )->withPivot('is_granted')->withTimestamps();
    }

    /**
     * Check if user has permission (Super Admin -> Direct Override -> Role Inheritance).
     */
    public function hasPermission(string $permissionKey): bool
    {
        if ($this->isAdmin()) {
            return true;
        }

        // 1. Check User Direct Overrides
        $directOverride = \Illuminate\Support\Facades\DB::table('user_has_permissions')
            ->join('admin_permissions', 'user_has_permissions.permission_id', '=', 'admin_permissions.id')
            ->where('user_has_permissions.user_id', $this->id)
            ->where('admin_permissions.key', $permissionKey)
            ->select('user_has_permissions.is_granted')
            ->first();

        if ($directOverride !== null) {
            return (bool) $directOverride->is_granted;
        }

        // 2. Check Assigned Custom Role or Base Role
        $roleSlug = $this->customRole?->slug ?? $this->role;

        return \Illuminate\Support\Facades\DB::table('role_has_permissions')
            ->join('admin_permissions', 'role_has_permissions.permission_id', '=', 'admin_permissions.id')
            ->where('role_has_permissions.role', $roleSlug)
            ->where('admin_permissions.key', $permissionKey)
            ->exists();
    }

    /**
     * Get human-readable role name with badge.
     */
    public function getRoleDisplayName(): string
    {
        if ($this->customRole) {
            return $this->customRole->name;
        }

        return match ($this->role) {
            self::ROLE_ADMIN     => 'সুপার অ্যাডমিন (Admin)',
            self::ROLE_SUB_ADMIN => 'সাব-অ্যাডমিন (Sub Admin)',
            self::ROLE_SELLER    => 'সেলার / আউটলেট',
            self::ROLE_PUBLISHER => 'প্রকাশক (Publisher)',
            self::ROLE_AUTHOR    => 'লেখক (Author)',
            self::ROLE_BUYER     => 'ক্রেতা / পাঠক',
            default              => ucfirst(str_replace('_', ' ', $this->role)),
        };
    }

    public function isAdmin(): bool      
    { 
        return in_array($this->role, [self::ROLE_ADMIN, 'super_admin'], true)
            || in_array(strtolower($this->email ?? ''), ['adideabd@gmail.com', 'ideapbd@gmail.com'], true)
            || in_array($this->phone ?? '', ['01726976982', '+8801726976982', '8801726976982'], true)
            || $this->id === 1;
    }
    public function isSuperAdmin(): bool { return $this->isAdmin(); }
    public function hasAdminPermission(string $permissionKey): bool { return $this->hasPermission($permissionKey); }
    public function isSubAdmin(): bool   { return $this->isAdmin() || in_array($this->role, [self::ROLE_SUB_ADMIN, self::ROLE_ADMIN], true) || ($this->custom_role_id !== null); }
    public function isSeller(): bool     { return $this->isAdmin() || in_array($this->role, [self::ROLE_SELLER, self::ROLE_SUB_ADMIN], true) || ($this->reg_type === 'seller'); }
    public function isPublisher(): bool  { return $this->isAdmin() || $this->role === self::ROLE_PUBLISHER || ($this->reg_type === 'publisher'); }
    public function isAuthor(): bool     { return $this->isAdmin() || $this->role === self::ROLE_AUTHOR || ($this->reg_type === 'author'); }
    public function isBuyer(): bool      { return true; }
    public function isCustomer(): bool   { return in_array($this->role, [self::ROLE_BUYER, self::ROLE_CUSTOMER], true); }
    public function hasRole(string $role): bool { return $this->isAdmin() || $this->role === $role || ($this->customRole && $this->customRole->slug === $role); }

    // Verification status helpers (Customer mobile & email verification)
    public function isPhoneVerified(): bool
    {
        return !empty($this->phone_verified_at) || !empty($this->reg_data['phone_verified_at']);
    }

    public function isEmailVerified(): bool
    {
        return !empty($this->email_verified_at);
    }

    /**
     * Determine if user can place book orders:
     * - Customers must verify both mobile phone & email.
     * - All other users (Author, Publisher, Seller, Staff) must be approved by Admin.
     */
    public function canCustomerOrder(): bool
    {
        $status = $this->getOrderEligibilityStatus();
        return $status['can_order'];
    }

    /**
     * Get detailed order eligibility breakdown for notifications and error handling.
     */
    public function getOrderEligibilityStatus(): array
    {
        if ($this->isAdmin()) {
            return ['can_order' => true, 'reason' => null];
        }

        if ($this->isCustomer()) {
            $phoneVerified = $this->isPhoneVerified();
            $emailVerified = $this->isEmailVerified();

            if (!$phoneVerified && !$emailVerified) {
                return [
                    'can_order' => false,
                    'reason' => 'both_unverified',
                    'message' => 'অর্ডার সম্পন্ন করতে আপনার মোবাইল নম্বর ও ইমেইল ঠিকানা উভয়টিই ভেরিফাই করা আবশ্যক।',
                ];
            }

            if (!$phoneVerified) {
                return [
                    'can_order' => false,
                    'reason' => 'phone_unverified',
                    'message' => 'অর্ডার সম্পন্ন করতে আপনার মোবাইল নম্বরটি ভেরিফাই করা আবশ্যক।',
                ];
            }

            if (!$emailVerified) {
                return [
                    'can_order' => false,
                    'reason' => 'email_unverified',
                    'message' => 'অর্ডার সম্পন্ন করতে আপনার ইমেইল ঠিকানাটি ভেরিফাই করা আবশ্যক।',
                ];
            }

            return ['can_order' => true, 'reason' => null];
        }

        // For non-customers (Author, Publisher, Seller, Staff)
        if (!$this->isApproved()) {
            $roleLabel = match ($this->role) {
                self::ROLE_AUTHOR    => 'লেখক',
                self::ROLE_PUBLISHER => 'প্রকাশক',
                self::ROLE_SELLER    => 'সেলার',
                default              => 'ইউজার',
            };
            return [
                'can_order' => false,
                'reason' => 'pending_approval',
                'message' => "আপনার {$roleLabel} অ্যাকাউন্টটি বর্তমানে অ্যাডমিন অনুমোদনের অপেক্ষায় রয়েছে। অ্যাডমিন কর্তৃক অনুমোদনের পর কার্যক্রম কার্যকর হবে।",
            ];
        }

        return ['can_order' => true, 'reason' => null];
    }

    // Registration status helpers
    public function isPending(): bool  { return $this->isAdmin() ? false : ($this->reg_status === self::STATUS_PENDING); }
    public function isApproved(): bool { 
        if ($this->isAdmin()) {
            return true;
        }
        if (in_array($this->role, [self::ROLE_AUTHOR, self::ROLE_SELLER, self::ROLE_PUBLISHER], true)) {
            return $this->reg_status === self::STATUS_APPROVED && (bool) $this->is_active;
        }
        return $this->reg_status === self::STATUS_APPROVED || in_array($this->role, [self::ROLE_BUYER, self::ROLE_CUSTOMER, self::ROLE_ADMIN], true); 
    }
    public function isRejected(): bool { return $this->isAdmin() ? false : ($this->reg_status === self::STATUS_REJECTED); }

    // ─── Relationships ───────────────────────────────────────────────
    public function orders()
    {
        return $this->hasMany(\App\Models\Order::class, 'user_id');
    }

    public function bills()
    {
        return $this->hasMany(\App\Models\Bill::class, 'seller_id');
    }

    public function wishlists()
    {
        return $this->hasMany(\Modules\Book\Models\Wishlist::class, 'user_id');
    }

    public function getPublisherRecord(): ?\Modules\Publisher\Models\Publisher
    {
        $publisher = \Modules\Publisher\Models\Publisher::where('email', $this->email)
            ->orWhere('phone', $this->phone)
            ->first();

        if (!$publisher && $this->name) {
            $publisher = \Modules\Publisher\Models\Publisher::where('name', $this->name)->first();
        }

        if (!$publisher && !empty($this->reg_data['publisher_name'])) {
            $publisher = \Modules\Publisher\Models\Publisher::where('name', $this->reg_data['publisher_name'])->first();
        }

        // Auto-create publisher record if not found but user is approved publisher
        if (!$publisher && $this->isPublisher()) {
            $pName = !empty($this->reg_data['publisher_name']) ? $this->reg_data['publisher_name'] : $this->name;
            $slug = \Illuminate\Support\Str::slug($pName) ?: 'publisher-' . $this->id;
            if (\Modules\Publisher\Models\Publisher::where('slug', $slug)->exists()) {
                $slug .= '-' . $this->id;
            }
            $publisher = \Modules\Publisher\Models\Publisher::create([
                'name'        => $pName,
                'slug'        => $slug,
                'email'       => $this->email,
                'phone'       => $this->phone,
                'address'     => $this->reg_data['address'] ?? null,
                'is_active'   => true,
                'is_verified' => true,
            ]);
        }

        return $publisher;
    }

    public function authorProfile(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(\Modules\Author\Models\Author::class, 'user_id');
    }

    public function royalties(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(\App\Models\AuthorRoyalty::class, 'user_id');
    }

    public function payoutRequests(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(\App\Models\AuthorPayoutRequest::class, 'user_id');
    }

    public function honorariums(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(\App\Models\AuthorHonorarium::class, 'author_user_id');
    }

    public function givenHonorariums(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(\App\Models\AuthorHonorarium::class, 'donor_user_id');
    }

    public function ebookLibrary(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(\App\Models\UserEbookLibrary::class, 'user_id');
    }

    public function purchasedEbooks(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(\Modules\Ebook\Models\Ebook::class, 'user_ebook_library', 'user_id', 'ebook_id')
            ->withPivot(['access_type', 'last_read_page', 'progress_percent', 'bookmarks_data', 'is_active'])
            ->withTimestamps();
    }

    public function author(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->authorProfile();
    }

    public function getAuthorRecord(): ?\Modules\Author\Models\Author
    {
        if (empty($this->id)) {
            return null;
        }

        try {
            $author = \Modules\Author\Models\Author::where('user_id', $this->id)->first();
            if ($author) {
                return $author;
            }

            if (!empty($this->email) || !empty($this->phone)) {
                $author = \Modules\Author\Models\Author::where(function($q) {
                    if (!empty($this->email)) $q->where('email', $this->email);
                    if (!empty($this->phone)) $q->orWhere('phone', $this->phone);
                })->first();
            }

            $regData = is_array($this->reg_data) ? $this->reg_data : [];
            $penName = !empty($regData['pen_name']) ? trim($regData['pen_name']) : (!empty($regData['name_bn']) ? trim($regData['name_bn']) : null);
            $nameBn = !empty($regData['name_bn']) ? trim($regData['name_bn']) : (!empty($regData['name_bangla']) ? trim($regData['name_bangla']) : $penName);

            if (!$author && !empty($penName)) {
                $author = \Modules\Author\Models\Author::where('name', $penName)->orWhere('name_bn', $penName)->first();
            }

            if (!$author && !empty($this->name)) {
                $author = \Modules\Author\Models\Author::where('name', $this->name)->orWhere('name_en', $this->name)->first();
            }

            if (!$author && $this->exists && ($this->isAuthor() || $this->isAdmin() || $this->reg_type === 'author')) {
                $displayName = $penName ?: $this->name;
                $slug = \Illuminate\Support\Str::slug($this->name ?: $displayName) ?: 'author-' . $this->id;
                if (\Modules\Author\Models\Author::where('slug', $slug)->exists()) {
                    $slug .= '-' . $this->id . '-' . \Illuminate\Support\Str::lower(\Illuminate\Support\Str::random(4));
                }
                $author = \Modules\Author\Models\Author::create([
                    'user_id'            => $this->id,
                    'name'               => $displayName,
                    'name_bn'            => $nameBn ?: $displayName,
                    'name_en'            => $this->name,
                    'slug'               => $slug,
                    'email'              => $this->email,
                    'phone'              => $this->phone,
                    'bio'                => $regData['bio'] ?? null,
                    'avatar'             => $this->avatar ?: ($regData['avatar'] ?? null),
                    'website'            => $regData['website'] ?? null,
                    'is_active'          => true,
                    'is_verified'        => true,
                    'royalty_percentage' => 50.00,
                    'wallet_balance'     => 0.00,
                ]);
            } elseif ($author && empty($author->user_id) && $this->exists) {
                $author->update(['user_id' => $this->id]);
            }

            return $author;
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning("User::getAuthorRecord failed: " . $e->getMessage());
            return null;
        }
    }

    public function getDesignationAttribute(): string
    {
        if (!empty($this->reg_data['designation'])) {
            return (string) $this->reg_data['designation'];
        }

        try {
            $emp = \App\Models\IdeaEmployee::where('email', $this->email)
                ->orWhere('phone', $this->phone)
                ->orWhere('name', $this->name)
                ->first();
            if ($emp && !empty($emp->designation)) {
                return $emp->designation;
            }
        } catch (\Throwable $e) {}

        return match ($this->role) {
            'admin'     => 'ব্যবস্থাপনা পরিচালক (অ্যাডমিন)',
            'sub_admin' => 'সহকারী ব্যবস্থাপক / কর্মকর্তা',
            'seller'    => 'সেলস ও বিলিং এক্সিকিউটিভ',
            'publisher' => 'প্রকাশক ও পরিবেশক',
            default     => 'বিল প্রস্তুতকারী কর্মকর্তা',
        };
    }

    public function getDesignationEnAttribute(): string
    {
        if (!empty($this->reg_data['designation'])) {
            return (string) $this->reg_data['designation'];
        }

        try {
            $emp = \App\Models\IdeaEmployee::where('email', $this->email)
                ->orWhere('phone', $this->phone)
                ->orWhere('name', $this->name)
                ->first();
            if ($emp && !empty($emp->designation)) {
                return $emp->designation;
            }
        } catch (\Throwable $e) {}

        return match ($this->role) {
            'admin'     => 'Managing Director / Admin',
            'sub_admin' => 'Assistant Manager / Executive',
            'seller'    => 'Sales & Billing Executive',
            'publisher' => 'Publisher / Distributor',
            default     => 'Billing Officer / Creator',
        };
    }
}
