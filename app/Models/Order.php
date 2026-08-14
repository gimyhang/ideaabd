<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', 'customer_name', 'customer_phone', 'customer_address', 'district',
        'is_gift', 'gift_recipient_name', 'gift_recipient_phone', 'gift_recipient_address', 'gift_message',
        'book_id', 'total_amount', 'status'
    ];

    public function book()
    {
        return $this->belongsTo(\Modules\Book\Models\Book::class, 'book_id');
    }
}
