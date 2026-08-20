<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\Schema;

class BookRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'customer_name',
        'customer_phone',
        'customer_email',
        'book_title',
        'author_name',
        'edition',
        'additional_info',
        'admin_notes',
        'status', // pending, processing, available, closed
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Auto-ensure extra columns exist in database if not yet migrated
     */
    protected static function booted(): void
    {
        static::saving(function (BookRequest $req) {
            try {
                if (!Schema::hasColumn('book_requests', 'admin_notes')) {
                    Schema::table('book_requests', function ($table) {
                        $table->text('admin_notes')->nullable()->after('additional_info');
                    });
                }
                if (!Schema::hasColumn('book_requests', 'customer_email')) {
                    Schema::table('book_requests', function ($table) {
                        $table->string('customer_email')->nullable()->after('customer_phone');
                    });
                }
                if (!Schema::hasColumn('book_requests', 'edition')) {
                    Schema::table('book_requests', function ($table) {
                        $table->string('edition')->nullable()->after('author_name');
                    });
                }
            } catch (\Throwable $e) {
                // Safe fallback
            }
        });
    }

    /**
     * Human-friendly status labels in Bengali
     */
    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'processing' => 'প্রক্রিয়াধীন (Processing)',
            'available'  => 'সংগৃহীত / প্রস্তুত (Available)',
            'closed'     => 'সম্পন্ন / বন্ধ (Closed)',
            default      => 'অপেক্ষমান (Pending)',
        };
    }

    /**
     * Bootstrap badge classes for statuses
     */
    public function getStatusBadgeClassAttribute(): string
    {
        return match ($this->status) {
            'processing' => 'bg-info-subtle text-info border border-info-subtle',
            'available'  => 'bg-success-subtle text-success border border-success-subtle',
            'closed'     => 'bg-secondary-subtle text-secondary border border-secondary-subtle',
            default      => 'bg-warning-subtle text-warning-emphasis border border-warning-subtle',
        };
    }

    /**
     * Status icon
     */
    public function getStatusIconAttribute(): string
    {
        return match ($this->status) {
            'processing' => 'fa-solid fa-spinner fa-spin',
            'available'  => 'fa-solid fa-circle-check',
            'closed'     => 'fa-solid fa-circle-xmark',
            default      => 'fa-solid fa-clock',
        };
    }

    /**
     * Clean phone for WhatsApp link
     */
    public function getCleanPhoneAttribute(): string
    {
        $phone = preg_replace('/[^0-9]/', '', (string)$this->customer_phone);
        if (str_starts_with($phone, '0')) {
            $phone = '88' . $phone;
        }
        return $phone;
    }

    /**
     * Search scope
     */
    public function scopeSearch($query, ?string $term)
    {
        if (empty($term)) {
            return $query;
        }

        return $query->where(function ($q) use ($term) {
            $like = '%' . $term . '%';
            $q->where('book_title', 'like', $like)
              ->orWhere('author_name', 'like', $like)
              ->orWhere('customer_name', 'like', $like)
              ->orWhere('customer_phone', 'like', $like)
              ->orWhere('additional_info', 'like', $like);
        });
    }

    public function scopeStatus($query, ?string $status)
    {
        if ($status && in_array($status, ['pending', 'processing', 'available', 'closed'], true)) {
            return $query->where('status', $status);
        }
        return $query;
    }
}
