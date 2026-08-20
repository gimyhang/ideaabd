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
        'role', 'avatar', 'is_active',
        'reg_status', 'reg_type', 'reg_data',
        'loyalty_points', 'affiliate_balance',
    ];

    protected $hidden = ['password', 'remember_token'];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password'          => 'hashed',
            'is_active'         => 'boolean',
            'reg_data'          => 'array',
            'loyalty_points'    => 'integer',
            'affiliate_balance' => 'decimal:2',
        ];
    }

    // ─── Role helpers ───────────────────────────────────────────────
    public function isAdmin(): bool      { return $this->role === self::ROLE_ADMIN; }
    public function isSubAdmin(): bool   { return $this->role === self::ROLE_SUB_ADMIN; }
    public function isSeller(): bool     { return in_array($this->role, [self::ROLE_SELLER, self::ROLE_SUB_ADMIN]); }
    public function isPublisher(): bool  { return $this->role === self::ROLE_PUBLISHER; }
    public function isAuthor(): bool     { return $this->role === self::ROLE_AUTHOR; }
    public function isBuyer(): bool      { return in_array($this->role, [self::ROLE_BUYER, self::ROLE_CUSTOMER]); }
    public function hasRole(string $role): bool { return $this->role === $role; }

    // Registration status helpers
    public function isPending(): bool  { return $this->reg_status === self::STATUS_PENDING; }
    public function isApproved(): bool { 
        if (in_array($this->role, [self::ROLE_AUTHOR, self::ROLE_SELLER, self::ROLE_PUBLISHER], true)) {
            return $this->reg_status === self::STATUS_APPROVED && (bool) $this->is_active;
        }
        return $this->reg_status === self::STATUS_APPROVED || in_array($this->role, [self::ROLE_BUYER, self::ROLE_CUSTOMER, self::ROLE_ADMIN], true); 
    }
    public function isRejected(): bool { return $this->reg_status === self::STATUS_REJECTED; }

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
}
