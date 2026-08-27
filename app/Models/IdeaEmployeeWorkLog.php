<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class IdeaEmployeeWorkLog extends Model
{
    use HasFactory;

    protected $table = 'idea_employee_work_logs';

    protected $fillable = [
        'employee_id',
        'entry_type',
        'log_date',
        'book_title',
        'quantity',
        'unit_rate',
        'unit_name',
        'earned_amount',
        'paid_amount',
        'payment_method',
        'voucher_no',
        'accounting_entry_id',
        'notes',
        'created_by',
    ];

    protected $casts = [
        'log_date'      => 'date',
        'quantity'      => 'decimal:2',
        'unit_rate'     => 'decimal:2',
        'earned_amount' => 'decimal:2',
        'paid_amount'   => 'decimal:2',
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
