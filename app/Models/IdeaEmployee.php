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

    /**
     * Determine staff role category for dynamic ledger adaptation.
     */
    public function getRoleCategory(): string
    {
        $text = mb_strtolower(($this->designation ?? '') . ' ' . ($this->department ?? '') . ' ' . ($this->skill_category ?? ''));

        if (str_contains($text, 'কম্পিউটার') || str_contains($text, 'computer') || str_contains($text, 'টাইপ') || str_contains($text, 'typeset') || str_contains($text, 'কম্পোজ') || str_contains($text, 'compos')) {
            return 'computer_operator';
        }
        if (str_contains($text, 'প্রুফ') || str_contains($text, 'proof') || str_contains($text, 'এডিটর') || str_contains($text, 'editor') || str_contains($text, 'সম্পাদনা') || str_contains($text, 'editorial')) {
            return 'proofreader';
        }
        if (str_contains($text, 'পিওন') || str_contains($text, 'peon') || str_contains($text, 'সহায়ক') || str_contains($text, 'assistant') || str_contains($text, 'হেল্পার') || str_contains($text, 'helper') || str_contains($text, 'ক্লিনার') || str_contains($text, 'messenger') || str_contains($text, 'mlss')) {
            return 'peon_helper';
        }
        if (str_contains($text, 'মার্কেটিং') || str_contains($text, 'marketing') || str_contains($text, 'সেলস') || str_contains($text, 'sales') || str_contains($text, 'রিপ্রেজেন্টেটিভ') || str_contains($text, 'field')) {
            return 'marketing';
        }
        if (str_contains($text, 'ডিজাইন') || str_contains($text, 'design') || str_contains($text, 'গ্রাফিক্স') || str_contains($text, 'graphics') || str_contains($text, 'কভার') || str_contains($text, 'cover')) {
            return 'designer';
        }
        if (str_contains($text, 'বাইন্ড') || str_contains($text, 'bind') || str_contains($text, 'ছাপা') || str_contains($text, 'press') || str_contains($text, 'কাটিং') || str_contains($text, 'cut') || str_contains($text, 'ল্যামিনেশন') || str_contains($text, 'কারিগর') || str_contains($text, 'artisan') || str_contains($text, 'folding')) {
            return 'artisan';
        }

        return 'general';
    }

    /**
     * Get role configuration with icon, badge, color theme, and default unit.
     */
    public function getRoleConfig(): array
    {
        $cat = $this->getRoleCategory();

        return match ($cat) {
            'computer_operator' => [
                'key'         => 'computer_operator',
                'title_bn'    => 'কম্পিউটার অপারেটর ও কম্পোজিটর লেজার',
                'title_en'    => 'Computer Operator & Typesetter Ledger',
                'icon'        => 'fa-solid fa-laptop-code',
                'bg_color'    => '#eff6ff',
                'text_color'  => '#1d4ed8',
                'border_color'=> '#bfdbfe',
                'accent_color'=> '#2563eb',
                'unit_default'=> 'Page (পৃষ্ঠা)',
                'units'       => ['Page (পৃষ্ঠা)', 'Forma (ফর্মা)', 'Book (বই)', 'Hour (ঘণ্টা)', 'Project (প্রজেক্ট)'],
                'work_label'  => 'টাইপসেটিং ও কম্পোজ কাজের বিবরণ',
            ],
            'proofreader' => [
                'key'         => 'proofreader',
                'title_bn'    => 'প্রুফ রিডার ও এডিটর লেজার',
                'title_en'    => 'Proofreader & Editorial Ledger',
                'icon'        => 'fa-solid fa-pen-nib',
                'bg_color'    => '#fefce8',
                'text_color'  => '#a16207',
                'border_color'=> '#fef08a',
                'accent_color'=> '#ca8a04',
                'unit_default'=> 'Forma (ফর্মা)',
                'units'       => ['Forma (ফর্মা)', 'Page (পৃষ্ঠা)', 'Book (বই)', 'Word (শব্দ)', 'Project (প্রজেক্ট)'],
                'work_label'  => 'প্রুফ রিডিং ও সম্পাদনাকৃত বই/ফাইল',
            ],
            'peon_helper' => [
                'key'         => 'peon_helper',
                'title_bn'    => 'অফিস সহায়ক, পিওন ও স্টাফ লেজার',
                'title_en'    => 'Office Assistant & Staff Ledger',
                'icon'        => 'fa-solid fa-person-walking-luggage',
                'bg_color'    => '#ecfdf5',
                'text_color'  => '#047857',
                'border_color'=> '#a7f3d0',
                'accent_color'=> '#059669',
                'unit_default'=> 'Day (হাজিরা / দিন)',
                'units'       => ['Day (হাজিরা / দিন)', 'Trip (ডেলিভারি ট্রিপ)', 'Hour (ওভারটাইম)', 'Month (মাস)', 'Task (কাজ)'],
                'work_label'  => 'অফিস ডিউটি, ডেলিভারি ও কাজের বিবরণ',
            ],
            'marketing' => [
                'key'         => 'marketing',
                'title_bn'    => 'মার্কেটিং অফিসার ও সেলস লেজার',
                'title_en'    => 'Marketing & Sales Executive Ledger',
                'icon'        => 'fa-solid fa-bullhorn',
                'bg_color'    => '#fff7ed',
                'text_color'  => '#c2410c',
                'border_color'=> '#fed7aa',
                'accent_color'=> '#ea580c',
                'unit_default'=> 'Month (মাসিক বেতন/ভাতা)',
                'units'       => ['Month (মাসিক বেতন/ভাতা)', 'Day (দৈনিক টিএ/ডিএ)', 'Outlet (বই শপ ভিজিট)', 'Commission (বিক্রয় কমিশন)', 'Campaign (ক্যাম্পেইন)'],
                'work_label'  => 'মার্কেটিং ক্যাম্পেইন, এরিয়া ও কাজের হিসাব',
            ],
            'designer' => [
                'key'         => 'designer',
                'title_bn'    => 'গ্রাফিক্স ও কভার ডিজাইনার লেজার',
                'title_en'    => 'Graphics & Cover Designer Ledger',
                'icon'        => 'fa-solid fa-palette',
                'bg_color'    => '#faf5ff',
                'text_color'  => '#7e22ce',
                'border_color'=> '#e9d5ff',
                'accent_color'=> '#9333ea',
                'unit_default'=> 'Cover (কভার ডিজাইন)',
                'units'       => ['Cover (কভার ডিজাইন)', 'Inner Layout (ইনার লেআউট)', 'Page (পৃষ্ঠা)', 'Book (সম্পূর্ণ বই)', 'Project (প্রজেক্ট)'],
                'work_label'  => 'ডিজাইনকৃত কভার, বই ও প্রজেক্ট',
            ],
            'artisan' => [
                'key'         => 'artisan',
                'title_bn'    => 'ছাপাখানা ও বাঁধাই কারিগর লেজার',
                'title_en'    => 'Book Binder & Press Artisan Ledger',
                'icon'        => 'fa-solid fa-book-bookmark',
                'bg_color'    => '#f3e8ff',
                'text_color'  => '#7e22ce',
                'border_color'=> '#d8b4fe',
                'accent_color'=> '#7e22ce',
                'unit_default'=> 'Book (বই বাঁধাই)',
                'units'       => ['Book (বই বাঁধাই)', 'Forma (ফর্মা ভাঁজ/পাস্টিং)', '1000 Sheets (কাটিং/ছাপা)', 'Lamination (ল্যামিনেশন)', 'Day (হাজিরা)'],
                'work_label'  => 'বই বাঁধাই ও ছাপাখানার কাজের হিসাব',
            ],
            default => [
                'key'         => 'general',
                'title_bn'    => 'স্টাফ কাজের খতিয়ান ও লেজার',
                'title_en'    => 'Employee Work & Financial Ledger',
                'icon'        => 'fa-solid fa-id-card-clip',
                'bg_color'    => '#f8fafc',
                'text_color'  => '#334155',
                'border_color'=> '#cbd5e1',
                'accent_color'=> '#475569',
                'unit_default'=> 'Day (দিন)',
                'units'       => ['Day (দিন)', 'Month (মাস)', 'Hour (ঘণ্টা)', 'Task (কাজ)', 'Project (প্রজেক্ট)'],
                'work_label'  => 'কাজের বিবরণ ও দায়িত্বের রেকর্ড',
            ],
        };
    }

    public static function departments(): array
    {
        return [
            'কম্পিউটার ও টাইপসেটিং (Computer & Typesetting)',
            'প্রুফ রিডিং ও সম্পাদনা (Proofreading & Editorial)',
            'ছাপাখানা ও বাঁধাই (Press & Book Binding)',
            'গ্রাফিক্স ও কভার ডিজাইন (Graphics & Cover Design)',
            'মার্কেটিং ও সেলস (Marketing & Sales)',
            'ডেলিভারি ও লজিস্টিকস (Delivery & Logistics)',
            'অফিস সার্ভিস ও পিওন (Office Support & Peon)',
            'অ্যাকাউন্টস ও ফাইন্যান্স (Accounts & Finance)',
            'আইটি ও ডিজিটাল মিডিয়া (IT & Digital Media)',
            'সাধারণ প্রশাসন (General Office & Admin)',
        ];
    }

    public static function employmentTypes(): array
    {
        return [
            'monthly'          => 'Monthly Salary (মাসিক স্থায়ী / চুক্তিভিত্তিক বেতন)',
            'contract_piece'   => 'Piece-Rate / Unit-Based (প্রতি পেজ/ফর্মা/বই/কাজ চুক্তি)',
            'daily'            => 'Daily Wage (দৈনিক হাজিরা / ডে-লেবার)',
            'weekly'           => 'Weekly Wage (সাপ্তাহিক মজুরি)',
            'contract_project' => 'Project / Freelance Basis (প্রজেক্ট চুক্তি)',
        ];
    }

    public static function rateTypes(): array
    {
        return [
            'monthly'       => 'Monthly Fixed Salary (মাসিক স্থায়ী বেতন)',
            'per_page'      => 'Per Page Rate (প্রতি পেজ / পৃষ্ঠা টাইপ ও প্রুফ দর)',
            'per_forma'     => 'Per Forma Rate (প্রতি ফর্মা কম্পোজ/প্রুফ/বাইন্ডিং দর)',
            'per_book'      => 'Per Book Binding Rate (প্রতি বই বাঁধাই দর)',
            'daily'         => 'Daily Wage Rate (দৈনিক হাজিরা দর)',
            'weekly'        => 'Weekly Wage Rate (সাপ্তাহিক মজুরি দর)',
            'per_thousand'  => 'Per 1,000 Sheets (প্রতি ১০০০ তা কাটিং/ছাপা দর)',
            'project_fixed' => 'Project Fixed Rate (প্রজেক্ট ভিত্তিক চুক্তি দর)',
        ];
    }

    public static function skillCategories(): array
    {
        return [
            'Computer Operator / Typesetter (কম্পিউটার অপারেটর ও কম্পোজিটর)',
            'Proofreader & Sub-Editor (প্রুফ রিডার ও সাব-এডিটর)',
            'Book Cover & Graphics Designer (গ্রাফিক্স ও কভার ডিজাইনার)',
            'Master Book Binder (মাস্টার বুক বাইন্ডার ও বাঁধাই কারিগর)',
            'Assistant Binder & Pasting Artisan (সহকারী বাইন্ডার ও পেস্টিং কারিগর)',
            'Paper Cutting Master (পেপার কাটিং মাস্টার)',
            'Offset Press Machine Operator (অফসেট প্রেস মেশিন অপারেটর)',
            'Lamination & Spot UV Specialist (ল্যামিনেশন ও স্পট ইউভি স্পেশালিস্ট)',
            'Marketing & Sales Officer (মার্কেটিং ও সেলস অফিসার)',
            'Office Assistant / Peon / MLSS (অফিস সহায়ক / পিওন)',
            'Delivery & Packaging Staff (ডেলিভারি ও প্যাকিং স্টাফ)',
            'General Office Staff (সাধারণ অফিস স্টাফ)',
        ];
    }
}
