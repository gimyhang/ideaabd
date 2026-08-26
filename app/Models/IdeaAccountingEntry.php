<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class IdeaAccountingEntry extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'entry_no',
        'type',
        'category',
        'title',
        'amount',
        'entry_date',
        'voucher_no',
        'payment_method',
        'party_name',
        'invoice_id',
        'notes',
        'created_by',
    ];

    protected $casts = [
        'amount'     => 'decimal:2',
        'entry_date' => 'date',
    ];

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(IdeaInvoice::class, 'invoice_id');
    }

    public static function productionCategories(): array
    {
        return [
            'কাগজ ক্রয় (Paper Purchase)',
            'বোর্ড ক্রয় (Binding Board Purchase)',
            'কালি ও প্লেট (Ink & Plates)',
            'মুদ্রণ ও প্রেস খরচ (Printing & Press)',
            'বাঁধাই ও লেমিনেশন (Binding & Lamination)',
            'ডিজাইন ও প্রুফরিডিং (Design & Proofing)',
        ];
    }

    public static function payrollCategories(): array
    {
        return [
            'কর্মচারী মূল বেতন (Staff Basic Salary)',
            'কর্মচারী বেতন ও ভাতা (Salary & Allowance)',
            'উৎসব ভাতা ও বোনাস (Festival Bonus & Allowance)',
            'ওভারটাইম ও দৈনিক মজুরি (Overtime & Daily Wages)',
        ];
    }

    public static function categories(): array
    {
        return [
            'expense' => [
                'কাগজ ক্রয় (Paper Purchase)',
                'বোর্ড ক্রয় (Binding Board Purchase)',
                'কালি ও প্লেট (Ink & Plates)',
                'মুদ্রণ ও প্রেস খরচ (Printing & Press)',
                'বাঁধাই ও লেমিনেশন (Binding & Lamination)',
                'ডিজাইন ও প্রুফরিডিং (Design & Proofing)',
                'অন্যান্য প্রকাশনীর বই ক্রয় (Other Publisher Books)',
                'প্যাকেজিং, কার্টুন ও পলি ব্যাগ (Packaging & Bags)',
                'স্টেশনারি, পিন ও সরঞ্জাম (Stationery, Pins & Tools)',
                'চা, নাস্তা ও পান আপ্যায়ন (Tea, Snacks & Refreshment)',
                'দৈনিক মজুরি ও লেবার খরচ (Daily Wages & Labor)',
                'কর্মচারী মূল বেতন (Staff Basic Salary)',
                'কর্মচারী বেতন ও ভাতা (Salary & Allowance)',
                'উৎসব ভাতা ও বোনাস (Festival Bonus & Allowance)',
                'ওভারটাইম ও অতিরিক্ত মজুরি (Overtime Wages)',
                'সম্মানী ও রয়্যালটি (Author Royalty & Honorarium)',
                'অফিস ভাড়া ও ইউটিলিটি (Office Rent & Utilities)',
                'পরিবহন ও কুরিয়ার (Transport & Courier)',
                'বিজ্ঞাপন ও প্রচারণা (Marketing & Promotion)',
                'মেরামত ও রক্ষণাবেক্ষণ (Maintenance)',
                'বিবিধ খরচ (Miscellaneous Expense)',
            ],
            'income' => [
                'বই বিক্রয় (Book Sales)',
                'পাইকারি বিক্রয় ও চালান (Wholesale Sales)',
                'পণ্য ও স্টেশনারি বিক্রয় (Goods & Stationery Sales)',
                'পাবলিকেশন সার্ভিস ফি (Publishing Services)',
                'ই-বুক ও ডিজিটাল কনটেন্ট (Digital Sales)',
                'বিজ্ঞাপন ও স্পন্সরশিপ (Sponsorship)',
                'বিবিধ আয় (Miscellaneous Income)',
            ],
        ];
    }
}
