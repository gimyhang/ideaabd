<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Modules\Book\Models\Book;
use Modules\Book\Models\Category;

class PublisherPurchaseItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'purchase_id',
        'item_type',
        'item_name',
        'size_spec',
        'unit',
        'quality_spec',
        'book_id',
        'book_title',
        'author_name',
        'category_id',
        'quantity',
        'reams_quantity',
        'mrp_price',
        'purchase_commission_percent',
        'unit_cost_price',
        'shop_discount_percent',
        'unit_sale_price',
        'subtotal',
        'item_notes',
    ];

    public function getDisplayNameAttribute(): string
    {
        if (!empty($this->item_name)) {
            return $this->item_name;
        }
        return $this->book_title ?: ($this->book->title ?? 'আইটেম');
    }

    protected $casts = [
        'quantity'                    => 'integer',
        'reams_quantity'              => 'decimal:3',
        'mrp_price'                   => 'decimal:2',
        'purchase_commission_percent' => 'decimal:2',
        'unit_cost_price'             => 'decimal:2',
        'shop_discount_percent'       => 'decimal:2',
        'unit_sale_price'             => 'decimal:2',
        'subtotal'                    => 'decimal:2',
    ];

    public function purchase()
    {
        return $this->belongsTo(PublisherPurchase::class, 'purchase_id');
    }

    public function book()
    {
        return $this->belongsTo(Book::class, 'book_id');
    }

    public function category()
    {
        return $this->belongsTo(Category::class, 'category_id');
    }
}
