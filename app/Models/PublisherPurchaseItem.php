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
        'book_id',
        'book_title',
        'author_name',
        'category_id',
        'quantity',
        'unit_cost_price',
        'unit_sale_price',
        'subtotal',
    ];

    protected $casts = [
        'quantity'        => 'integer',
        'unit_cost_price' => 'decimal:2',
        'unit_sale_price' => 'decimal:2',
        'subtotal'        => 'decimal:2',
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
