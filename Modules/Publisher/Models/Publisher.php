<?php

namespace Modules\Publisher\Models;

use App\Models\Concerns\Moderatable;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Modules\Book\Models\Book;

class Publisher extends Model
{
    use HasFactory, SoftDeletes, Moderatable;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'logo',
        'email',
        'phone',
        'website',
        'address',
        'country',
        'social_links',
        'is_verified',
        'is_active',
    ];

    protected $casts = [
        'social_links' => 'json',
        'is_verified' => 'boolean',
        'is_active' => 'boolean',
    ];

    public function books()
    {
        return $this->hasMany(Book::class, 'publisher_id');
    }

    public function ebooks()
    {
        return $this->hasMany(\Modules\Ebook\Models\Ebook::class, 'publisher_id');
    }

    public function purchases()
    {
        return $this->hasMany(\App\Models\PublisherPurchase::class, 'publisher_id');
    }

    public function payments()
    {
        return $this->hasMany(\App\Models\PublisherPayment::class, 'publisher_id');
    }
}
