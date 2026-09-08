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

        if (str_contains($text, 'digital marketing') || str_contains($text, 'মার্কেটিং') || str_contains($text, 'marketing') || str_contains($text, 'seo') || str_contains($text, 'social media') || str_contains($text, 'ads') || str_contains($text, 'campaign') || str_contains($text, 'বিজ্ঞাপন')) {
            return 'digital_marketing';
        }
        if (str_contains($text, 'content') || str_contains($text, 'editorial') || str_contains($text, 'প্রুফ') || str_contains($text, 'proof') || str_contains($text, 'এডিটর') || str_contains($text, 'editor') || str_contains($text, 'সম্পাদনা') || str_contains($text, 'লেখক') || str_contains($text, 'writer') || str_contains($text, 'অনুবাদ') || str_contains($text, 'typeset') || str_contains($text, 'কম্পোজ')) {
            return 'content_editorial';
        }
        if (str_contains($text, 'technical') || str_contains($text, 'it') || str_contains($text, 'developer') || str_contains($text, 'software') || str_contains($text, 'টেকনিক্যাল') || str_contains($text, 'আইটি') || str_contains($text, 'কম্পিউটার') || str_contains($text, 'computer') || str_contains($text, 'system') || str_contains($text, 'database') || str_contains($text, 'programmer')) {
            return 'technical_it';
        }
        if (str_contains($text, 'operations') || str_contains($text, 'support') || str_contains($text, 'অপারেশন') || str_contains($text, 'সাপোর্ট') || str_contains($text, 'customer') || str_contains($text, 'কাস্টমার') || str_contains($text, 'logistics') || str_contains($text, 'লজিস্টিক') || str_contains($text, 'dispatch') || str_contains($text, 'ডেলিভারি') || str_contains($text, 'peon') || str_contains($text, 'সহায়ক') || str_contains($text, 'পিওন')) {
            return 'operations_support';
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
            'digital_marketing' => [
                'key'         => 'digital_marketing',
                'title_bn'    => 'ডিজিটাল মার্কেটিং ও গ্রোথ টিম লেজার',
                'title_en'    => 'Digital Marketing & Growth Executive Ledger',
                'icon'        => 'fa-solid fa-bullhorn',
                'bg_color'    => '#eff6ff',
                'text_color'  => '#1d4ed8',
                'border_color'=> '#bfdbfe',
                'accent_color'=> '#2563eb',
                'unit_default'=> 'Month (মাসিক বেতন/ভাতা)',
                'units'       => ['Month (মাসিক বেতন/ভাতা)', 'Campaign (ক্যাম্পেইন)', 'Day (দৈনিক টিএ/ডিএ)', 'Project (প্রজেক্ট)', 'Commission (বিক্রয় কমিশন)'],
                'work_label'  => 'ডিজিটাল মার্কেটিং ক্যাম্পেইন, সোশ্যাল মিডিয়া ও অ্যাড রিপোর্ট',
            ],
            'content_editorial' => [
                'key'         => 'content_editorial',
                'title_bn'    => 'কনটেন্ট ও সম্পাদকীয় টিম লেজার',
                'title_en'    => 'Content & Editorial Department Ledger',
                'icon'        => 'fa-solid fa-feather-pointed',
                'bg_color'    => '#fefce8',
                'text_color'  => '#a16207',
                'border_color'=> '#fef08a',
                'accent_color'=> '#ca8a04',
                'unit_default'=> 'Forma (ফর্মা)',
                'units'       => ['Forma (ফর্মা)', 'Page (পৃষ্ঠা)', 'Book (বই)', 'Word (শব্দ)', 'Month (মাস)', 'Project (প্রজেক্ট)'],
                'work_label'  => 'প্রুফ রিডিং, সম্পাদনা ও পাণ্ডুলিপি পর্যালোচনার কাজের হিসাব',
            ],
            'technical_it' => [
                'key'         => 'technical_it',
                'title_bn'    => 'টেকনিক্যাল ও আইটি ইঞ্জিনিয়ারিং লেজার',
                'title_en'    => 'Technical & IT Engineering Ledger',
                'icon'        => 'fa-solid fa-laptop-code',
                'bg_color'    => '#f0fdf4',
                'text_color'  => '#15803d',
                'border_color'=> '#bbf7d0',
                'accent_color'=> '#16a34a',
                'unit_default'=> 'Month (মাস)',
                'units'       => ['Month (মাস)', 'Project (প্রজেক্ট)', 'Feature (ফিচার ডেভ)', 'Hour (ঘণ্টা)', 'Task (টাস্ক)'],
                'work_label'  => 'সফটওয়্যার ডেভেলপমেন্ট, সিস্টেম ও আইটি রক্ষণাবেক্ষণ লগ',
            ],
            'operations_support' => [
                'key'         => 'operations_support',
                'title_bn'    => 'অপারেশন্স ও কাস্টমার সাপোর্ট লেজার',
                'title_en'    => 'Operations & Customer Support Ledger',
                'icon'        => 'fa-solid fa-headset',
                'bg_color'    => '#fff7ed',
                'text_color'  => '#c2410c',
                'border_color'=> '#fed7aa',
                'accent_color'=> '#ea580c',
                'unit_default'=> 'Month (মাস)',
                'units'       => ['Month (মাস)', 'Day (হাজিরা / দিন)', 'Order (অর্ডার প্রসেসিং)', 'Trip (ডেলিভারি ট্রিপ)', 'Task (কাজ)'],
                'work_label'  => 'অপারেশনস, কাস্টমার সাপোর্ট ও ডেলিভারি কাজের হিসাব',
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
            'Digital Marketing (ডিজিটাল মার্কেটিং)',
            'Content & Editorial (কনটেন্ট ও সম্পাদকীয়)',
            'Technical & IT (টেকনিক্যাল ও আইটি)',
            'Operations & Support (অপারেশনস ও সাপোর্ট)',
            'কম্পিউটার ও টাইপসেটিং (Computer & Typesetting)',
            'প্রুফ রিডিং ও সম্পাদনা (Proofreading & Editorial)',
            'ছাপাখানা ও বাঁধাই (Press & Book Binding)',
            'গ্রাফিক্স ও কভার ডিজাইন (Graphics & Cover Design)',
            'মার্কেটিং ও সেলস (Marketing & Sales)',
            'ডেলিভারি ও লজিস্টিকস (Delivery & Logistics)',
            'অফিস সার্ভিস ও পিওন (Office Support & Peon)',
            'অ্যাকাউন্টস ও ফাইন্যান্স (Accounts & Finance)',
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
            // 1. Digital Marketing Class
            'Digital Marketing Specialist & Media Buyer (ডিজিটাল মার্কেটিং ও মিডিয়া বায়ার)',
            'SEO & Social Media Campaign Manager (এসইও ও সোশ্যাল মিডিয়া ম্যানেজার)',
            'Content Marketer & Copywriter (মার্কেটিং কনটেন্ট ও কপিরাইটার)',

            // 2. Content & Editorial Class
            'Executive Editor & Content Lead (প্রধান সম্পাদক ও কনটেন্ট লিড)',
            'Proofreader & Sub-Editor (প্রুফ রিডার ও সাব-এডিটর)',
            'Book Layout & Typesetter (বই লেআউট ও কম্পোজিটর)',
            'Translator & Manuscript Reviewer (অনুবাদক ও পাণ্ডুলিপি পর্যালোচক)',

            // 3. Technical & IT Class
            'Full-Stack Web & Software Developer (সফটওয়্যার ও ওয়েব ডেভেলপার)',
            'IT Support & System Administrator (আইটি সাপোর্ট ও সিস্টেম অ্যাডমিন)',
            'UI/UX & Graphics Designer (ইউআই/ইউএক্স ও গ্রাফিক্স ডিজাইনার)',
            'Database & Server Administrator (ডাটাবেজ ও ক্লাউড ইঞ্জিনিয়ার)',

            // 4. Operations & Support Class
            'Customer Support & CRM Executive (কাস্টমার সাপোর্ট ও সিআরএম এক্সিকিউটিভ)',
            'Order Fulfillment & Dispatch Officer (অর্ডার প্রসেসিং ও ডিসপ্যাচ অফিসার)',
            'Supply Chain & Logistics Lead (সাপ্লাই চেইন ও লজিস্টিকস)',
            'Office Assistant / Peon / MLSS (অফিস সহায়ক / পিওন)',

            // Press & Production Artisans
            'Book Cover & Graphics Designer (গ্রাফিক্স ও কভার ডিজাইনার)',
            'Master Book Binder (মাস্টার বুক বাইন্ডার ও বাঁধাই কারিগর)',
            'Assistant Binder & Pasting Artisan (সহকারী বাইন্ডার ও পেস্টিং কারিগর)',
            'Paper Cutting Master (পেপার কাটিং মাস্টার)',
            'Offset Press Machine Operator (অফসেট প্রেস মেশিন অপারেটর)',
            'Lamination & Spot UV Specialist (ল্যামিনেশন ও স্পট ইউভি স্পেশালিস্ট)',
            'General Office Staff (সাধারণ অফিস স্টাফ)',
        ];
    }
}
