<?php

declare(strict_types=1);

namespace Modules\BulkOrder\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Modules\User\Models\User;

class BulkOrder extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'bulk_orders';

    /**
     * Mass assignable attributes.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'order_type', // 'educational', 'commercial', 'bulk_purchase'
        'institution_name',
        'institution_type', // 'school', 'college', 'university', 'library', 'bookstore'
        'contact_person',
        'email',
        'phone',
        'address',
        'quantity',
        'total_amount',
        'discount_percentage',
        'special_requirements',
        'status', // 'pending', 'approved', 'rejected', 'completed'
        'notes',
        'requested_at',
        'estimated_delivery_date',
        'is_invoice_required',
    ];

    /**
     * Attribute Type Casting
     *
     * @var array<string, string>
     */
    protected $casts = [
        'quantity' => 'integer',
        'total_amount' => 'decimal:2',
        'discount_percentage' => 'decimal:2',
        'is_invoice_required' => 'boolean',
        'requested_at' => 'datetime',
        'estimated_delivery_date' => 'date',
    ];

    /**
     * User Relationship.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Items in this order.
     */
    public function items(): HasMany
    {
        return $this->hasMany(BulkOrderItem::class);
    }

    /**
     * Scope for pending orders.
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    /**
     * Scope for approved orders.
     */
    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }

    /**
     * Get institutional type label.
     */
    public function getInstitutionTypeLabel(): string
    {
        return match ($this->institution_type) {
            'school' => 'School',
            'college' => 'College',
            'university' => 'University',
            'library' => 'Library',
            'bookstore' => 'Bookstore',
            default => 'Institution',
        };
    }

    /**
     * Get order type label.
     */
    public function getOrderTypeLabel(): string
    {
        return match ($this->order_type) {
            'educational' => 'Educational',
            'commercial' => 'Commercial',
            'bulk_purchase' => 'Bulk Purchase',
            default => 'Other',
        };
    }

    /**
     * Calculate final amount after discount.
     */
    public function getFinalAmountAttribute(): float
    {
        $discount = ($this->total_amount * $this->discount_percentage) / 100;
        return $this->total_amount - $discount;
    }
}
