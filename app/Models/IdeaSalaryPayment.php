<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class IdeaSalaryPayment extends Model
{
    use HasFactory;

    protected $table = 'idea_salary_payments';

    protected $fillable = [
        'employee_id',
        'salary_month',
        'payment_date',
        'basic_amount',
        'bonus_amount',
        'overtime_amount',
        'deduction_amount',
        'net_paid',
        'payment_method',
        'trx_reference',
        'slip_no',
        'accounting_entry_id',
        'notes',
        'created_by',
    ];

    protected $casts = [
        'payment_date'     => 'date',
        'basic_amount'     => 'decimal:2',
        'bonus_amount'     => 'decimal:2',
        'overtime_amount'  => 'decimal:2',
        'deduction_amount' => 'decimal:2',
        'net_paid'         => 'decimal:2',
    ];

    public function employee(): BelongsTo
    {
        return $this->belongsTo(IdeaEmployee::class, 'employee_id');
    }

    public function accountingEntry(): BelongsTo
    {
        return $this->belongsTo(IdeaAccountingEntry::class, 'accounting_entry_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
