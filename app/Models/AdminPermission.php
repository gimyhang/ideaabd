<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AdminPermission extends Model
{
    use HasFactory;

    protected $fillable = [
        'key',
        'name',
        'module',
        'description',
    ];

    public function roles()
    {
        return $this->belongsToMany(User::class, 'role_has_permissions', 'permission_id', 'role');
    }
}
