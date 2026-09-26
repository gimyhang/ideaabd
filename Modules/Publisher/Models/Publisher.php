<?php

namespace Modules\Publisher\Models;

use App\Models\Concerns\Moderatable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Modules\Book\Models\Book;
use Illuminate\Support\Str;

class Publisher extends Model
{
    use HasFactory, SoftDeletes, Moderatable;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'logo',
        'email',
        'phone',
        'website',
        'address',
        'country',
        'social_links',
        'is_verified',
        'is_active',
    ];

    protected $casts = [
        'social_links' => 'json',
        'is_verified'  => 'boolean',
        'is_active'    => 'boolean',
    ];

    protected $appends = [
        'logo_url',
        'initials',
        'logo_bg_color',
    ];

    /**
     * Dynamic Logo URL with path normalization, storage asset routing, and protocol safety
     */
    public function getLogoUrlAttribute(): ?string
    {
        $logo = $this->logo ?? null;
        if (empty($logo)) {
            return null;
        }

        $logo = trim((string) $logo);
        $logo = str_replace('\\', '/', $logo);

        // If it's inline data URI
        if (str_starts_with($logo, 'data:image')) {
            return $logo;
        }

        // If it's an absolute URL
        if (str_starts_with($logo, 'http://') || str_starts_with($logo, 'https://')) {
            $parsed = parse_url($logo);
            $host = $parsed['host'] ?? '';
            $path = $parsed['path'] ?? '';

            if (in_array($host, ['127.0.0.1', 'localhost', 'ideaabd.com', 'www.ideaabd.com'], true) || str_contains($path, '/storage/')) {
                $cleanPath = ltrim($path, '/');
                if (str_starts_with($cleanPath, 'storage/')) {
                    return asset($cleanPath);
                }
                return asset('storage/' . $cleanPath);
            }

            return $logo;
        }

        // Clean leading slashes
        $cleanPath = ltrim($logo, '/');

        if (str_starts_with($cleanPath, 'storage/')) {
            return asset($cleanPath);
        }

        if (str_starts_with($cleanPath, 'public/')) {
            return asset('storage/' . substr($cleanPath, 7));
        }

        return asset('storage/' . $cleanPath);
    }

    /**
     * Unicode-safe Publisher initials (Bengali & English)
     */
    public function getInitialsAttribute(): string
    {
        $name = trim($this->name ?? '');
        if (empty($name)) {
            return 'প্র';
        }

        $words = preg_split('/\s+/u', $name);
        if (count($words) >= 2) {
            return mb_substr($words[0], 0, 1) . mb_substr($words[1], 0, 1);
        }

        return mb_substr($name, 0, 1);
    }

    /**
     * Deterministic pleasant gradient background for publisher logo placeholder
     */
    public function getLogoBgColorAttribute(): string
    {
        $colors = [
            'linear-gradient(135deg, #0284c7 0%, #0369a1 100%)',
            'linear-gradient(135deg, #0d9488 0%, #0f766e 100%)',
            'linear-gradient(135deg, #4f46e5 0%, #4338ca 100%)',
            'linear-gradient(135deg, #d97706 0%, #b45309 100%)',
            'linear-gradient(135deg, #e11d48 0%, #be123c 100%)',
            'linear-gradient(135deg, #7c3aed 0%, #6d28d9 100%)',
            'linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%)',
            'linear-gradient(135deg, #334155 0%, #1e293b 100%)',
        ];

        $idx = abs(crc32((string) ($this->id ?? $this->name ?? '1'))) % count($colors);
        return $colors[$idx];
    }

    public function books()
    {
        return $this->hasMany(Book::class, 'publisher_id');
    }

    public function ebooks()
    {
        return $this->hasMany(\Modules\Ebook\Models\Ebook::class, 'publisher_id');
    }

    public function purchases()
    {
        return $this->hasMany(\App\Models\PublisherPurchase::class, 'publisher_id');
    }

    public function payments()
    {
        return $this->hasMany(\App\Models\PublisherPayment::class, 'publisher_id');
    }

    /**
     * UNIFIED PUBLISHER FIND OR CREATE / SYNC
     * Ensures a single publisher record exists and is synced with user registration
     */
    public static function findOrCreateUnified(array $data): self
    {
        $name = trim($data['name'] ?? $data['publishing_house_name'] ?? '');
        $email = !empty($data['email']) ? trim(strtolower((string) $data['email'])) : null;
        $phone = !empty($data['phone']) ? trim((string) $data['phone']) : null;
        $slug = !empty($data['slug']) ? trim(\Illuminate\Support\Str::slug((string) $data['slug'])) : null;
        $logo = $data['logo'] ?? null;
        $address = $data['address'] ?? null;
        $country = $data['country'] ?? 'Bangladesh';

        $publisher = null;

        // 1. Search by exact or trimmed Name
        if (!empty($name)) {
            $publisher = self::where('name', $name)
                ->orWhere(\Illuminate\Support\Facades\DB::raw('TRIM(name)'), $name)
                ->first();
        }

        // 2. Search by Phone (if provided)
        if (!$publisher && !empty($phone)) {
            $publisher = self::where('phone', $phone)->first();
        }

        // 3. Search by Email (if provided)
        if (!$publisher && !empty($email)) {
            $publisher = self::where('email', $email)->first();
        }

        // 4. Search by Slug (if provided)
        if (!$publisher && !empty($slug)) {
            $publisher = self::where('slug', $slug)->first();
        }

        if ($publisher) {
            $updates = [];
            if (empty($publisher->email) && $email) {
                $updates['email'] = $email;
            }
            if (empty($publisher->phone) && $phone) {
                $updates['phone'] = $phone;
            }
            if (empty($publisher->address) && $address) {
                $updates['address'] = $address;
            }
            if (empty($publisher->country) && $country) {
                $updates['country'] = $country;
            }
            if (!empty($logo) && empty($publisher->logo)) {
                $updates['logo'] = $logo;
            }
            if (!empty($updates)) {
                $publisher->update($updates);
            }
            return $publisher;
        }

        if (empty($slug)) {
            $rawSlug = \Illuminate\Support\Str::slug($name);
            if (empty($rawSlug)) {
                $rawSlug = 'publisher-' . time() . '-' . rand(100, 999);
            }
            $slug = $rawSlug;
            $count = 1;
            while (self::where('slug', $slug)->exists()) {
                $slug = $rawSlug . '-' . $count++;
            }
        }

        return self::create([
            'name'        => $name,
            'slug'        => $slug,
            'email'       => $email,
            'phone'       => $phone,
            'address'     => $address,
            'country'     => $country,
            'description' => $data['description'] ?? null,
            'logo'        => $logo,
            'website'     => $data['website'] ?? null,
            'is_verified' => !empty($data['is_verified']),
            'is_active'   => $data['is_active'] ?? false,
        ]);
    }
}
