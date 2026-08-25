<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\Author\Models\Author;
use Modules\Blog\Models\BlogPost;

class AuthorHonorarium extends Model
{
    use HasFactory;

    protected $table = 'author_honorariums';

    protected $fillable = [
        'author_id',
        'author_user_id',
        'blog_post_id',
        'donor_user_id',
        'donor_name',
        'donor_phone',
        'donor_email',
        'message',
        'amount',
        'platform_fee',
        'author_amount',
        'payment_method',
        'payment_channel',
        'sender_account_number',
        'trx_id',
        'payment_status',
        'is_anonymous',
        'admin_notes',
    ];

    protected $casts = [
        'amount'        => 'decimal:2',
        'platform_fee'  => 'decimal:2',
        'author_amount' => 'decimal:2',
        'is_anonymous'  => 'boolean',
    ];

    public function author(): BelongsTo
    {
        return $this->belongsTo(Author::class, 'author_id');
    }

    public function authorUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'author_user_id');
    }

    public function post(): BelongsTo
    {
        return $this->belongsTo(BlogPost::class, 'blog_post_id');
    }

    public function donor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'donor_user_id');
    }

    public function scopeCompleted($query)
    {
        return $query->where('payment_status', 'completed');
    }

    public function scopePending($query)
    {
        return $query->where('payment_status', 'pending');
    }

    public function getDisplayNameAttribute(): string
    {
        if ($this->is_anonymous) {
            return 'শুভাকাঙ্ক্ষী পাঠক (গোপন)';
        }
        return $this->donor_name ?: 'সম্মানিত পাঠক';
    }

    public function getMethodBadgeClassAttribute(): string
    {
        return match(strtolower((string)$this->payment_method)) {
            'bkash'      => 'bg-danger text-white',
            'nagad'      => 'bg-warning text-dark',
            'rocket'     => 'bg-purple text-white',
            'card', 'sslcommerz' => 'bg-info text-white',
            default      => 'bg-secondary text-white',
        };
    }

    public function getStatusBadgeClassAttribute(): string
    {
        return match(strtolower((string)$this->payment_status)) {
            'completed' => 'bg-success text-white',
            'pending'   => 'bg-warning text-dark',
            'rejected'  => 'bg-danger text-white',
            'refunded'  => 'bg-dark text-white',
            default     => 'bg-secondary text-white',
        };
    }
}
