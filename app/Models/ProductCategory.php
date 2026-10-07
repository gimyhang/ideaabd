<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class ProductCategory extends Model
{
    use HasFactory;

    protected $table = 'product_categories';

    protected $fillable = [
        'type',
        'name',
        'slug',
        'description',
        'icon',
        'image',
        'is_active',
        'sort_order',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($category) {
            if (empty($category->slug)) {
                $category->slug = Str::slug($category->name);
            }
        });
    }

    public function products(): HasMany
    {
        return $this->hasMany(Product::class, 'category_id');
    }

    public function scopeElectronics($query)
    {
        return $query->where('type', 'electronics');
    }

    public function scopeStationery($query)
    {
        return $query->where('type', 'stationery');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Dynamic Icon Details for Category Bubble Slider & Badges
     *
     * @return array{type: string, value: string, bg: string, shadow: string, color: string}
     */
    public function getIconDetailsAttribute(): array
    {
        $rawIcon = trim((string)($this->icon ?? ''));
        $rawImage = trim((string)($this->image ?? ''));

        if (!empty($rawImage)) {
            $url = (str_starts_with($rawImage, 'http://') || str_starts_with($rawImage, 'https://'))
                ? $rawImage
                : asset($rawImage);
            return [
                'type' => 'image',
                'value' => $url,
                'bg' => '#ffffff',
                'shadow' => 'rgba(2, 132, 199, 0.25)',
                'color' => '#0284c7',
            ];
        }

        if (!empty($rawIcon)) {
            $iconClass = str_starts_with($rawIcon, 'fa-') ? "fa-solid {$rawIcon}" : $rawIcon;
            return [
                'type' => 'icon',
                'value' => $iconClass,
                'bg' => 'linear-gradient(135deg, #0284c7 0%, #0369a1 100%)',
                'shadow' => 'rgba(2, 132, 199, 0.35)',
                'color' => '#ffffff',
            ];
        }

        $name = mb_strtolower((string)$this->name);
        $slug = mb_strtolower((string)$this->slug);
        $combo = $name . ' ' . $slug;

        $map = [
            ['keys' => ['রাইস', 'কুকার', 'cooker', 'rice'], 'icon' => 'fa-solid fa-kitchen-set', 'bg' => 'linear-gradient(135deg, #f97316 0%, #ea580c 100%)', 'shadow' => 'rgba(249, 115, 22, 0.38)'],
            ['keys' => ['চুলা', 'ইন্ডাকশন', 'ইনফ্রারেড', 'induction', 'infrared', 'stove'], 'icon' => 'fa-solid fa-fire-burner', 'bg' => 'linear-gradient(135deg, #dc2626 0%, #b91c1c 100%)', 'shadow' => 'rgba(220, 38, 38, 0.38)'],
            ['keys' => ['ফ্যান', 'রিচার্জেবল', 'fan'], 'icon' => 'fa-solid fa-fan', 'bg' => 'linear-gradient(135deg, #0284c7 0%, #0369a1 100%)', 'shadow' => 'rgba(2, 132, 199, 0.38)'],
            ['keys' => ['ব্লেন্ডার', 'জুসার', 'মিক্সার', 'blender', 'juicer', 'grinder'], 'icon' => 'fa-solid fa-blender', 'bg' => 'linear-gradient(135deg, #8b5cf6 0%, #7c3aed 100%)', 'shadow' => 'rgba(139, 92, 246, 0.38)'],
            ['keys' => ['চার্জার', 'অ্যাডাপ্টার', 'ক্যাবল', 'charger', 'adapter', 'cable'], 'icon' => 'fa-solid fa-bolt', 'bg' => 'linear-gradient(135deg, #eab308 0%, #ca8a04 100%)', 'shadow' => 'rgba(234, 179, 8, 0.38)'],
            ['keys' => ['পাওয়ার ব্যাংক', 'ব্যাটারি', 'power bank', 'battery'], 'icon' => 'fa-solid fa-battery-three-quarters', 'bg' => 'linear-gradient(135deg, #10b981 0%, #059669 100%)', 'shadow' => 'rgba(16, 185, 129, 0.38)'],
            ['keys' => ['মাল্টিপ্লাগ', 'সকেট', 'এক্সটেনশন', 'multiplug', 'socket', 'extension'], 'icon' => 'fa-solid fa-plug', 'bg' => 'linear-gradient(135deg, #06b6d4 0%, #0891b2 100%)', 'shadow' => 'rgba(6, 182, 212, 0.38)'],
            ['keys' => ['হেডফোন', 'ইয়ারবাড', 'স্পিকার', 'অডিও', 'headphone', 'earphone', 'speaker', 'audio'], 'icon' => 'fa-solid fa-headphones', 'bg' => 'linear-gradient(135deg, #ec4899 0%, #db2777 100%)', 'shadow' => 'rgba(236, 72, 153, 0.38)'],
            ['keys' => ['লাইট', 'ল্যাম্প', 'টর্চ', 'light', 'lamp', 'torch'], 'icon' => 'fa-solid fa-lightbulb', 'bg' => 'linear-gradient(135deg, #f59e0b 0%, #d97706 100%)', 'shadow' => 'rgba(245, 158, 11, 0.38)'],
            ['keys' => ['কেটলি', 'জগ', 'ফ্লাস্ক', 'kettle', 'flask'], 'icon' => 'fa-solid fa-mug-hot', 'bg' => 'linear-gradient(135deg, #64748b 0%, #475569 100%)', 'shadow' => 'rgba(100, 116, 139, 0.38)'],
            ['keys' => ['খাতা', 'ডায়েরি', 'নোটবুক', 'notebook', 'diary'], 'icon' => 'fa-solid fa-book-open', 'bg' => 'linear-gradient(135deg, #059669 0%, #047857 100%)', 'shadow' => 'rgba(5, 150, 105, 0.38)'],
            ['keys' => ['কলম', 'পেন', 'মার্কার', 'pen', 'marker'], 'icon' => 'fa-solid fa-pen-nib', 'bg' => 'linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%)', 'shadow' => 'rgba(37, 99, 235, 0.38)'],
            ['keys' => ['সিল', 'স্ট্যাম্প', 'stamp', 'seal'], 'icon' => 'fa-solid fa-stamp', 'bg' => 'linear-gradient(135deg, #dc2626 0%, #991b1b 100%)', 'shadow' => 'rgba(220, 38, 38, 0.38)'],
            ['keys' => ['ফাইল', 'ফোল্ডার', 'file', 'folder'], 'icon' => 'fa-solid fa-folder-open', 'bg' => 'linear-gradient(135deg, #d97706 0%, #b45309 100%)', 'shadow' => 'rgba(217, 119, 6, 0.38)'],
            ['keys' => ['ক্যালেন্ডার', 'calendar'], 'icon' => 'fa-solid fa-calendar-days', 'bg' => 'linear-gradient(135deg, #7c3aed 0%, #5b21b6 100%)', 'shadow' => 'rgba(124, 58, 237, 0.38)'],
        ];

        foreach ($map as $item) {
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

        $palettes = [
            ['icon' => 'fa-solid fa-layer-group', 'bg' => 'linear-gradient(135deg, #0284c7 0%, #0369a1 100%)', 'shadow' => 'rgba(2, 132, 199, 0.35)'],
            ['icon' => 'fa-solid fa-microchip', 'bg' => 'linear-gradient(135deg, #6366f1 0%, #4f46e5 100%)', 'shadow' => 'rgba(99, 102, 241, 0.35)'],
            ['icon' => 'fa-solid fa-plug-circle-bolt', 'bg' => 'linear-gradient(135deg, #059669 0%, #047857 100%)', 'shadow' => 'rgba(5, 150, 105, 0.35)'],
            ['icon' => 'fa-solid fa-box-open', 'bg' => 'linear-gradient(135deg, #d97706 0%, #b45309 100%)', 'shadow' => 'rgba(217, 119, 6, 0.35)'],
            ['icon' => 'fa-solid fa-cubes', 'bg' => 'linear-gradient(135deg, #8b5cf6 0%, #7c3aed 100%)', 'shadow' => 'rgba(139, 92, 246, 0.35)'],
        ];
        $idx = abs(crc32((string)($this->id . $this->name))) % count($palettes);
        $sel = $palettes[$idx];

        return [
            'type' => 'icon',
            'value' => $sel['icon'],
            'bg' => $sel['bg'],
            'shadow' => $sel['shadow'],
            'color' => '#ffffff',
        ];
    }
}
