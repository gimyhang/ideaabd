<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\Author\Models\Author;

class AuthorPayoutRequest extends Model
{
    use HasFactory;

    protected $table = 'author_payout_requests';

    protected $fillable = [
        'author_id',
        'user_id',
        'amount',
        'payment_method',
        'gateway_channel',
        'account_details',
        'tax_deduction_amount',
        'gateway_fee',
        'net_payable_amount',
        'status',
        'admin_notes',
        'rejection_reason',
        'transaction_ref',
        'gateway_response',
        'processed_by',
        'processed_at',
    ];

    protected $casts = [
        'amount'               => 'decimal:2',
        'tax_deduction_amount' => 'decimal:2',
        'gateway_fee'          => 'decimal:2',
        'net_payable_amount'   => 'decimal:2',
        'gateway_response'     => 'array',
        'processed_at'         => 'datetime',
    ];

    public function author(): BelongsTo
    {
        return $this->belongsTo(Author::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function processor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'processed_by');
    }
}
