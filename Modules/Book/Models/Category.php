<?php

declare(strict_types=1);

namespace Modules\Book\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Category extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'categories';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int,string>
     */
    protected $fillable = [
        'parent_id',
        'name',
        'slug',
        'description',
        'icon_or_image',
        'meta_title',
        'meta_description',
        'sort_order',
        'is_active',
        'is_featured',
    ];

    /**
     * The attribute type casts.
     *
     * @var array<string,string>
     */
    protected $casts = [
        'sort_order' => 'integer',
        'is_active' => 'boolean',
        'is_featured' => 'boolean',
    ];

    /**
     * Parent category (self-referencing)
     */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    /**
     * Child categories
     */
    public function children(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id');
    }

    /**
     * Books in this category
     */
    public function books(): HasMany
    {
        return $this->hasMany(Book::class, 'category_id');
    }

    /**
     * Recent books for cover slideshow / carousel
     */
    public function recentBooks(): HasMany
    {
        return $this->hasMany(Book::class, 'category_id')
            ->select(['id', 'category_id', 'title', 'cover_image'])
            ->whereNotNull('cover_image')
            ->where('cover_image', '!=', '')
            ->latest('id');
    }

    /**
     * Ebooks in this category
     */
    public function ebooks(): HasMany
    {
        return $this->hasMany(\Modules\Ebook\Models\Ebook::class, 'category_id');
    }

    /**
     * Dynamic Icon Details for 75px Category Display (Icon Class, Image URL, Gradients, Shadows)
     *
     * @return array{type: string, value: string, bg: string, shadow: string, color: string}
     */
    public function getIconDetailsAttribute(): array
    {
        $raw = trim((string) ($this->icon_or_image ?? ''));

        // 1. Direct Image URL / Storage Path
        if (!empty($raw) && (str_starts_with($raw, 'http://') || str_starts_with($raw, 'https://') || str_starts_with($raw, '/') || str_starts_with($raw, 'storage/') || str_starts_with($raw, 'assets/') || str_starts_with($raw, 'images/'))) {
            $url = (str_starts_with($raw, 'http://') || str_starts_with($raw, 'https://')) ? $raw : asset($raw);
            return [
                'type' => 'image',
                'value' => $url,
                'bg' => '#f8fafc',
                'shadow' => 'rgba(0, 102, 204, 0.25)',
                'color' => '#0066cc',
            ];
        }

        // 2. Direct FontAwesome / Bootstrap Icon Class
        if (!empty($raw) && (str_starts_with($raw, 'fa-') || str_starts_with($raw, 'fas ') || str_starts_with($raw, 'far ') || str_starts_with($raw, 'bi-'))) {
            $iconClass = str_starts_with($raw, 'fa-') ? "fa-solid {$raw}" : $raw;
            return [
                'type' => 'icon',
                'value' => $iconClass,
                'bg' => 'linear-gradient(135deg, #0284c7 0%, #0369a1 100%)',
                'shadow' => 'rgba(2, 132, 199, 0.35)',
                'color' => '#ffffff',
            ];
        }

        // 3. Intelligent Bengali & English Genre Keyword Matcher
        $name = mb_strtolower((string) $this->name);
        $slug = mb_strtolower((string) $this->slug);
        $combo = $name . ' ' . $slug;

        $genreMap = [
            ['keys' => ['উপন্যাস', 'fiction', 'novel'], 'icon' => 'fa-solid fa-book-open-reader', 'bg' => 'linear-gradient(135deg, #4f46e5 0%, #7c3aed 100%)', 'shadow' => 'rgba(79, 70, 229, 0.38)'],
            ['keys' => ['কবিতা', 'কাব্য', 'poetry', 'poem'], 'icon' => 'fa-solid fa-feather', 'bg' => 'linear-gradient(135deg, #ec4899 0%, #be185d 100%)', 'shadow' => 'rgba(236, 72, 153, 0.38)'],
            ['keys' => ['গল্প', 'কথাসাহিত্য', 'প্রবন্ধ', 'story', 'essay'], 'icon' => 'fa-solid fa-book-bookmark', 'bg' => 'linear-gradient(135deg, #f59e0b 0%, #d97706 100%)', 'shadow' => 'rgba(245, 158, 11, 0.38)'],
            ['keys' => ['মুক্তিযুদ্ধ', 'ইতিহাস', 'ঐতিহ্য', 'history', 'war'], 'icon' => 'fa-solid fa-monument', 'bg' => 'linear-gradient(135deg, #dc2626 0%, #991b1b 100%)', 'shadow' => 'rgba(220, 38, 38, 0.38)'],
            ['keys' => ['ইসলাম', 'ধর্ম', 'কোরআন', 'হাদিস', 'islam', 'religion'], 'icon' => 'fa-solid fa-mosque', 'bg' => 'linear-gradient(135deg, #059669 0%, #047857 100%)', 'shadow' => 'rgba(5, 150, 105, 0.38)'],
            ['keys' => ['বিজ্ঞান', 'সাই-ফাই', 'কল্পবিজ্ঞান', 'science', 'sci-fi'], 'icon' => 'fa-solid fa-atom', 'bg' => 'linear-gradient(135deg, #0284c7 0%, #0369a1 100%)', 'shadow' => 'rgba(2, 132, 199, 0.38)'],
            ['keys' => ['শিশু', 'কিশোর', 'কমিকস', 'কার্টুন', 'children', 'kids', 'comic'], 'icon' => 'fa-solid fa-shapes', 'bg' => 'linear-gradient(135deg, #f97316 0%, #ea580c 100%)', 'shadow' => 'rgba(249, 115, 22, 0.38)'],
            ['keys' => ['থ্রিলার', 'গোয়েন্দা', 'রহস্য', 'সাসপেন্স', 'thriller', 'mystery', 'detective'], 'icon' => 'fa-solid fa-mask', 'bg' => 'linear-gradient(135deg, #334155 0%, #0f172a 100%)', 'shadow' => 'rgba(51, 65, 85, 0.45)'],
            ['keys' => ['আত্মউন্নয়ন', 'মোটিভেশন', 'অনুপ্রেরণা', 'স্বাবলম্বন', 'self-help', 'motivation'], 'icon' => 'fa-solid fa-fire-flame-curved', 'bg' => 'linear-gradient(135deg, #ff5722 0%, #e65100 100%)', 'shadow' => 'rgba(255, 87, 34, 0.38)'],
            ['keys' => ['ক্যারিয়ার', 'চাকরি', 'বিসিএস', 'পেশা', 'career', 'job', 'bcs'], 'icon' => 'fa-solid fa-briefcase', 'bg' => 'linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%)', 'shadow' => 'rgba(37, 99, 235, 0.38)'],
            ['keys' => ['দর্শন', 'সমাজবিজ্ঞান', 'রাজনীতি', 'philosophy', 'sociology', 'politics'], 'icon' => 'fa-solid fa-brain', 'bg' => 'linear-gradient(135deg, #7c3aed 0%, #5b21b6 100%)', 'shadow' => 'rgba(124, 58, 237, 0.38)'],
            ['keys' => ['জীবনী', 'স্মৃতিকথা', 'আত্মজীবনী', 'biography', 'memoir'], 'icon' => 'fa-solid fa-user-pen', 'bg' => 'linear-gradient(135deg, #d97706 0%, #b45309 100%)', 'shadow' => 'rgba(217, 119, 6, 0.38)'],
            ['keys' => ['অনুবাদ', 'বিদেশি', 'translation', 'translated'], 'icon' => 'fa-solid fa-language', 'bg' => 'linear-gradient(135deg, #0d9488 0%, #0f766e 100%)', 'shadow' => 'rgba(13, 148, 136, 0.38)'],
            ['keys' => ['গণিত', 'অলিম্পিয়াড', 'ধাঁধা', 'math', 'olympiad'], 'icon' => 'fa-solid fa-square-root-variable', 'bg' => 'linear-gradient(135deg, #6366f1 0%, #4338ca 100%)', 'shadow' => 'rgba(99, 102, 241, 0.38)'],
            ['keys' => ['প্রযুক্তি', 'কম্পিউটার', 'প্রোগ্রামিং', 'tech', 'coding', 'computer'], 'icon' => 'fa-solid fa-laptop-code', 'bg' => 'linear-gradient(135deg, #0891b2 0%, #0e7490 100%)', 'shadow' => 'rgba(8, 145, 178, 0.38)'],
            ['keys' => ['ব্যবসা', 'উদ্যোক্তা', 'অর্থনীতি', 'business', 'economics', 'finance', 'startup'], 'icon' => 'fa-solid fa-chart-line', 'bg' => 'linear-gradient(135deg, #16a34a 0%, #15803d 100%)', 'shadow' => 'rgba(22, 163, 74, 0.38)'],
            ['keys' => ['রান্না', 'খাদ্য', 'রেসিপি', 'cooking', 'food', 'recipe'], 'icon' => 'fa-solid fa-utensils', 'bg' => 'linear-gradient(135deg, #f43f5e 0%, #e11d48 100%)', 'shadow' => 'rgba(244, 63, 94, 0.38)'],
            ['keys' => ['স্বাস্থ্য', 'চিকিৎসা', 'মেডিকেল', 'health', 'medical', 'fitness'], 'icon' => 'fa-solid fa-heart-pulse', 'bg' => 'linear-gradient(135deg, #10b981 0%, #059669 100%)', 'shadow' => 'rgba(16, 185, 129, 0.38)'],
            ['keys' => ['শিক্ষা', 'অভিধান', 'রেফারেন্স', 'education', 'dictionary', 'reference'], 'icon' => 'fa-solid fa-graduation-cap', 'bg' => 'linear-gradient(135deg, #0ea5e9 0%, #0284c7 100%)', 'shadow' => 'rgba(14, 165, 233, 0.38)'],
            ['keys' => ['আইন', 'অধিকার', 'সংবিধান', 'law', 'justice'], 'icon' => 'fa-solid fa-scale-balanced', 'bg' => 'linear-gradient(135deg, #475569 0%, #334155 100%)', 'shadow' => 'rgba(71, 85, 105, 0.38)'],
            ['keys' => ['ভ্রমণ', 'পর্যটন', 'travel', 'tour'], 'icon' => 'fa-solid fa-plane-departure', 'bg' => 'linear-gradient(135deg, #14b8a6 0%, #0d9488 100%)', 'shadow' => 'rgba(20, 184, 166, 0.38)'],
            ['keys' => ['শিল্প', 'সংস্কৃতি', 'চলচ্চিত্র', 'art', 'culture', 'cinema', 'music'], 'icon' => 'fa-solid fa-palette', 'bg' => 'linear-gradient(135deg, #d946ef 0%, #c026d3 100%)', 'shadow' => 'rgba(217, 70, 239, 0.38)'],
            ['keys' => ['পরিবেশ', 'কৃষি', 'প্রকৃতি', 'গাছপালা', 'environment', 'agriculture', 'nature'], 'icon' => 'fa-solid fa-seedling', 'bg' => 'linear-gradient(135deg, #84cc16 0%, #65a30d 100%)', 'shadow' => 'rgba(132, 204, 22, 0.38)'],
            ['keys' => ['সাময়িকী', 'পত্রিকা', 'ম্যাগাজিন', 'magazine', 'journal'], 'icon' => 'fa-solid fa-newspaper', 'bg' => 'linear-gradient(135deg, #64748b 0%, #475569 100%)', 'shadow' => 'rgba(100, 116, 139, 0.38)'],
            ['keys' => ['ই-বুক', 'অডিও', 'ebook', 'digital'], 'icon' => 'fa-solid fa-tablet-screen-button', 'bg' => 'linear-gradient(135deg, #8b5cf6 0%, #6d28d9 100%)', 'shadow' => 'rgba(139, 92, 246, 0.38)'],
        ];

        foreach ($genreMap as $item) {
            foreach ($item['keys'] as $k) {
                if (str_contains($combo, $k)) {
                    return [
                        'type' => 'icon',
                        'value' => $item['icon'],
                        'bg' => $item['bg'],
                        'shadow' => $item['shadow'],
                        'color' => '#ffffff',
                    ];
                }
            }
        }

        // 4. Dynamic Palette Fallback based on category ID / Name hash
        $fallbackPalettes = [
            ['icon' => 'fa-solid fa-book', 'bg' => 'linear-gradient(135deg, #0284c7 0%, #0369a1 100%)', 'shadow' => 'rgba(2, 132, 199, 0.35)'],
            ['icon' => 'fa-solid fa-bookmark', 'bg' => 'linear-gradient(135deg, #6366f1 0%, #4f46e5 100%)', 'shadow' => 'rgba(99, 102, 241, 0.35)'],
            ['icon' => 'fa-solid fa-layer-group', 'bg' => 'linear-gradient(135deg, #059669 0%, #047857 100%)', 'shadow' => 'rgba(5, 150, 105, 0.35)'],
            ['icon' => 'fa-solid fa-book-journal-whills', 'bg' => 'linear-gradient(135deg, #d97706 0%, #b45309 100%)', 'shadow' => 'rgba(217, 119, 6, 0.35)'],
            ['icon' => 'fa-solid fa-book-open', 'bg' => 'linear-gradient(135deg, #dc2626 0%, #b91c1c 100%)', 'shadow' => 'rgba(220, 38, 38, 0.35)'],
            ['icon' => 'fa-solid fa-feather-pointed', 'bg' => 'linear-gradient(135deg, #8b5cf6 0%, #7c3aed 100%)', 'shadow' => 'rgba(139, 92, 246, 0.35)'],
            ['icon' => 'fa-solid fa-graduation-cap', 'bg' => 'linear-gradient(135deg, #0d9488 0%, #0f766e 100%)', 'shadow' => 'rgba(13, 148, 136, 0.35)'],
            ['icon' => 'fa-solid fa-compass', 'bg' => 'linear-gradient(135deg, #ea580c 0%, #c2410c 100%)', 'shadow' => 'rgba(234, 88, 12, 0.35)'],
        ];

        $idx = abs(crc32((string) ($this->id . $this->name))) % count($fallbackPalettes);
        $selected = $fallbackPalettes[$idx];

        return [
            'type' => 'icon',
            'value' => $selected['icon'],
            'bg' => $selected['bg'],
            'shadow' => $selected['shadow'],
            'color' => '#ffffff',
        ];
    }
}
