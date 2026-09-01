<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class BookBundle extends Model
{
    protected $fillable = [
        'title',
        'slug',
        'description',
        'banner_image',
        'regular_price',
        'bundle_price',
        'discount_percent',
        'is_featured',
        'is_active',
    ];

    protected $casts = [
        'regular_price'    => 'decimal:2',
        'bundle_price'     => 'decimal:2',
        'discount_percent' => 'decimal:2',
        'is_featured'      => 'boolean',
        'is_active'        => 'boolean',
    ];

    public function items(): HasMany
    {
        return $this->hasMany(BundleItem::class, 'bundle_id');
    }
}
