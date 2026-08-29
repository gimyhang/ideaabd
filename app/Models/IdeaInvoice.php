<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class IdeaInvoice extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'invoice_no',
        'access_token',
        'type',
        'sales_category',
        'subject',
        'reference_no',
        'customer_name',
        'customer_designation',
        'customer_org',
        'customer_email',
        'customer_phone',
        'customer_address',
        'invoice_date',
        'valid_until',
        'items',
        'subtotal',
        'discount',
        'tax',
        'grand_total',
        'paid_amount',
        'due_amount',
        'payment_method',
        'payment_status',
        'notes',
        'terms_conditions',
        'emailed_at',
        'created_by',
    ];

    public function getCategoryLabelAttribute(): string
    {
        return match($this->sales_category) {
            'stationery'     => 'স্টেশনারী বিক্রয় (Stationery)',
            'printing_goods' => 'প্রিন্টিং ও সেবা (Printing Goods & Services)',
            'other'          => 'অন্যান্য ও বিবিধ (Other Sales)',
            default          => 'বই বিক্রয় (Books)',
        };
    }

    public function getCategoryBadgeAttribute(): array
    {
        return match($this->sales_category) {
            'stationery'     => ['bg' => 'bg-info-subtle text-info border-info-subtle', 'label' => 'স্টেশনারী'],
            'printing_goods' => ['bg' => 'bg-warning-subtle text-warning-emphasis border-warning-subtle', 'label' => 'প্রিন্টিং গুডস'],
            'other'          => ['bg' => 'bg-secondary-subtle text-secondary border-secondary-subtle', 'label' => 'অন্যান্য'],
            default          => ['bg' => 'bg-primary-subtle text-primary border-primary-subtle', 'label' => 'বই বিক্রয়'],
        };
    }

    protected $casts = [
        'items'        => 'array',
        'invoice_date' => 'date',
        'valid_until'  => 'date',
        'emailed_at'   => 'datetime',
        'subtotal'     => 'decimal:2',
        'discount'     => 'decimal:2',
        'tax'          => 'decimal:2',
        'grand_total'  => 'decimal:2',
        'paid_amount'  => 'decimal:2',
        'due_amount'   => 'decimal:2',
    ];

    protected static function booted(): void
    {
        static::creating(function ($invoice) {
            if (empty($invoice->access_token)) {
                $invoice->access_token = \Illuminate\Support\Str::random(32);
            }
        });
    }

    public static function ensureColumnsExist(): void
    {
        try {
            if (\Illuminate\Support\Facades\Schema::hasTable('idea_invoices')) {
                $schema = \Illuminate\Support\Facades\Schema::connection(null);
                \Illuminate\Support\Facades\Schema::table('idea_invoices', function (\Illuminate\Database\Schema\Blueprint $table) use ($schema) {
                    if (!$schema->hasColumn('idea_invoices', 'customer_email')) {
                        $table->string('customer_email', 255)->nullable();
                    }
                    if (!$schema->hasColumn('idea_invoices', 'customer_designation')) {
                        $table->string('customer_designation', 150)->nullable();
                    }
                    if (!$schema->hasColumn('idea_invoices', 'access_token')) {
                        $table->string('access_token', 64)->nullable();
                    }
                    if (!$schema->hasColumn('idea_invoices', 'emailed_at')) {
                        $table->timestamp('emailed_at')->nullable();
                    }
                    if (!$schema->hasColumn('idea_invoices', 'customer_org')) {
                        $table->string('customer_org', 255)->nullable();
                    }
                    if (!$schema->hasColumn('idea_invoices', 'subject')) {
                        $table->string('subject', 255)->nullable();
                    }
                    if (!$schema->hasColumn('idea_invoices', 'reference_no')) {
                        $table->string('reference_no', 100)->nullable();
                    }
                    if (!$schema->hasColumn('idea_invoices', 'valid_until')) {
                        $table->date('valid_until')->nullable();
                    }
                    if (!$schema->hasColumn('idea_invoices', 'terms_conditions')) {
                        $table->text('terms_conditions')->nullable();
                    }
                });
            }
        } catch (\Throwable $e) {
            // Ignore if already added or restricted
        }
    }

    public function getPublicUrlAttribute(): string
    {
        return route('invoices.public.show', $this->access_token ?: $this->invoice_no);
    }

    public function getTypeLabelAttribute(): string
    {
        return match ($this->type) {
            'challan'   => 'ডেলিভারি চালান',
            'quotation' => 'কোটেশন / প্রফর্মা',
            'tender'    => 'দরপত্র / প্রস্তাবনা',
            default     => 'বিল / ক্যাশ মেমো',
        };
    }

    public function getTypeBadgeClassAttribute(): string
    {
        return match ($this->type) {
            'challan'   => 'bg-info-subtle text-dark border-info',
            'quotation' => 'bg-warning-subtle text-dark border-warning',
            'tender'    => 'bg-purple-subtle text-purple border-purple',
            default     => 'bg-success-subtle text-success border-success',
        };
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function getCreatorNameAttribute(): string
    {
        return $this->creator?->name ?? 'Admin / Authority';
    }

    public function getCreatorDesignationAttribute(): string
    {
        return $this->creator?->designation ?? 'বিল প্রস্তুতকারী / হিসাব কর্মকর্তা';
    }

    public function getCreatorDesignationEnAttribute(): string
    {
        return $this->creator?->designation_en ?? 'Authorized Signatory / Billing Officer';
    }

    public function accountingEntries(): HasMany
    {
        return $this->hasMany(IdeaAccountingEntry::class, 'invoice_id');
    }
}
