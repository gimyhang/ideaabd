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

    // Registration pending?
    public function isPending(): bool  { return $this->reg_status === self::STATUS_PENDING; }
    public function isApproved(): bool { return $this->reg_status === self::STATUS_APPROVED || $this->role === self::ROLE_CUSTOMER; }
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
}
