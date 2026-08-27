<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class IdeaEmployee extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'idea_employees';

    protected $fillable = [
        'name',
        'designation',
        'department',
        'employment_type',
        'skill_category',
        'phone',
        'email',
        'basic_salary',
        'salary_rate_type',
        'rate_unit_name',
        'payment_schedule',
        'joining_date',
        'status',
        'address',
        'nid_passport',
        'emergency_contact',
        'notes',
    ];

    protected $casts = [
        'basic_salary' => 'decimal:2',
        'joining_date' => 'date',
    ];

    public function salaryPayments(): HasMany
    {
        return $this->hasMany(IdeaSalaryPayment::class, 'employee_id')->latest('payment_date');
    }

    public function workLogs(): HasMany
    {
        return $this->hasMany(IdeaEmployeeWorkLog::class, 'employee_id')->latest('log_date')->latest('id');
    }

    public function totalPaidSalary(): float
    {
        return (float) $this->salaryPayments()->sum('net_paid');
    }

    public function totalWorkEarned(): float
    {
        return (float) $this->workLogs()->where('entry_type', 'work')->sum('earned_amount');
    }

    public function totalWorkPaid(): float
    {
        return (float) $this->workLogs()->where('entry_type', 'payment')->sum('paid_amount');
    }

    public function currentWorkBalance(): float
    {
        return $this->totalWorkEarned() - $this->totalWorkPaid();
    }

    /**
     * Get human-readable formatted rate badge string.
     */
    public function getFormattedRateAttribute(): string
    {
        $amount = '৳' . number_format($this->basic_salary, 2);
        $type = $this->salary_rate_type ?? 'monthly';
        $unit = $this->rate_unit_name;

        if ($type === 'per_book') {
            return $amount . ' / Book Binding';
        } elseif ($type === 'per_forma') {
            return $amount . ' / Forma';
        } elseif ($type === 'per_thousand') {
            return $amount . ' / 1,000 Sheets';
        } elseif ($type === 'per_page') {
            return $amount . ' / Page';
        } elseif ($type === 'daily') {
            return $amount . ' / Day (Daily Wage)';
        } elseif ($type === 'weekly') {
            return $amount . ' / Week';
        } elseif ($type === 'project_fixed') {
            return $amount . ' / Project';
        }

        return $amount . ($unit ? " / {$unit}" : ' / Month');
    }

    public static function departments(): array
    {
        return [
            'Press & Book Binding',
            'Editorial & Publishing',
            'Design & Pre-Press',
            'Marketing & Sales',
            'Delivery & Logistics',
            'Accounts & Finance',
            'IT & Digital Media',
            'General Office & Admin',
        ];
    }

    public static function employmentTypes(): array
    {
        return [
            'monthly'          => 'Monthly Salary (Permanent / Contract)',
            'daily'            => 'Daily Wage (Day-to-day / Attendance)',
            'weekly'           => 'Weekly Wage',
            'contract_piece'   => 'Piece-Rate / Book Binding (Per Book/Forma/Page)',
            'contract_project' => 'Project / Contract Basis',
        ];
    }

    public static function rateTypes(): array
    {
        return [
            'monthly'       => 'Monthly Fixed Salary Rate',
            'daily'         => 'Daily Wage Rate (Per Day)',
            'weekly'        => 'Weekly Wage Rate',
            'per_book'      => 'Per Book Binding Rate (৳ / Book)',
            'per_forma'     => 'Per Forma / Sheet Binding Rate (৳ / Forma)',
            'per_thousand'  => 'Per 1,000 Sheets Printing/Cutting Rate',
            'per_page'      => 'Per Page Typesetting/Design Rate',
            'project_fixed' => 'Project Fixed Rate',
        ];
    }

    public static function skillCategories(): array
    {
        return [
            'Master Book Binder',
            'Assistant Binder & Pasting Artisan',
            'Paper Cutting Master',
            'Offset Press Machine Operator',
            'Assistant Press Machine Operator / Helper',
            'Lamination & Spot UV Specialist',
            'Book Cover & Graphics Designer',
            'Typesetter / Page Compositor',
            'Proofreader & Sub-Editor',
            'Marketing & Sales Representative',
            'Delivery & Packaging Staff',
            'Office Assistant & General Staff',
        ];
    }
}
