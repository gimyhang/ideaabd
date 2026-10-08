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
            'কাগজ ক্রয়',
            'বোর্ড ক্রয়',
            'কালি ও প্লেট',
            'মুদ্রণ ও প্রেস',
            'বাঁধাই ও লেমিনেশন',
            'ডিজাইন ও প্রুফ',
        ];
    }

    public static function payrollCategories(): array
    {
        return [
            'কর্মচারী মূল বেতন',
            'কর্মচারী বেতন ও ভাতা',
            'উৎসব ভাতা ও বোনাস',
            'ওভারটাইম মজুরি',
        ];
    }

    public static function categories(): array
    {
        return [
            'expense' => [
                'কাগজ ক্রয়',
                'বোর্ড ক্রয়',
                'কালি ও প্লেট',
                'মুদ্রণ ও প্রেস',
                'বাঁধাই ও লেমিনেশন',
                'ডিজাইন ও প্রুফ',
                'অন্যান্য বই ক্রয়',
                'প্যাকেজিং ও ব্যাগ',
                'স্টেশনারি ও সরঞ্জাম',
                'চা ও আপ্যায়ন',
                'দৈনিক মজুরি',
                'কর্মচারী মূল বেতন',
                'কর্মচারী বেতন ও ভাতা',
                'উৎসব ভাতা ও বোনাস',
                'ওভারটাইম মজুরি',
                'সম্মানী ও রয়্যালটি',
                'অফিস ভাড়া ও ইউটিলিটি',
                'পরিবহন ও কুরিয়ার',
                'বিজ্ঞাপন ও প্রচারণা',
                'মেরামত ও রক্ষণাবেক্ষণ',
                'বিবিধ খরচ',
            ],
            'income' => [
                'বই বিক্রয়',
                'পাইকারি বিক্রয়',
                'পণ্য বিক্রয়',
                'প্রকাশনা সার্ভিস',
                'ই-বুক ও ডিজিটাল',
                'বিজ্ঞাপন ও স্পন্সর',
                'বিবিধ আয়',
            ],
        ];
    }
}
