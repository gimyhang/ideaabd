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
        'phone',
        'email',
        'basic_salary',
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

    public function totalPaidSalary(): float
    {
        return (float) $this->salaryPayments()->sum('net_paid');
    }

    public static function departments(): array
    {
        return [
            'সম্পাদনা ও প্রকাশনা (Editorial & Publishing)',
            'ডিজাইন ও প্রুফরিডিং (Design & Proofing)',
            'ছাপাখানা ও বাঁধাই (Press & Binding)',
            'মার্কেটিং ও সেলস (Marketing & Sales)',
            'ডেলিভারি ও লজিস্টিকস (Delivery & Logistics)',
            'হিসাব ও অর্থায়ন (Accounts & Finance)',
            'আইটি ও ডিজিটাল মিডিয়া (IT & Digital Media)',
            'অফিস স্টাফ ও ব্যবস্থাপনা (General Office Staff)',
        ];
    }
}
