<?php

namespace Modules\Book\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\Rule;

/**
 * Single source of truth for book genres. The admin form, the publisher form, validation,
 * filters and SEO all read from here instead of keeping their own copies of the list.
 * Books store the genre's slug in books.genre_category, so renaming a genre never touches book rows.
 */
class Genre extends Model
{
    /** Used to seed the table and as a fallback while the migration has not run yet. */
    public const DEFAULTS = [
        'novel'               => ['Novel', 'উপন্যাস'],
        'story'               => ['Stories', 'গল্পগ্রন্থ'],
        'poetry'              => ['Poetry', 'কবিতা'],
        'essay_research'      => ['Essays', 'প্রবন্ধ ও গবেষণা'],
        'history_liberation'  => ['History & Liberation', 'মুক্তিযুদ্ধ ও ইতিহাস'],
        'islamic'             => ['Islamic', 'ইসলামিক ও ধর্মীয়'],
        'juvenile_comics'     => ['Juvenile & Comics', 'শিশু-কিশোর ও কমিক্স'],
        'scifi_thriller'      => ['Sci-Fi & Thriller', 'সায়েন্স ফিকশন ও থ্রিলার'],
        'motivation_selfhelp' => ['Motivation', 'আত্মউন্নয়ন ও মোটিভেশন'],
        'translated'          => ['Translation', 'অনুবাদ সাহিত্য'],
    ];

    private const CACHE_KEY = 'genres.all';

    protected $table = 'genres';

    protected $fillable = ['slug', 'name_en', 'name_bn', 'icon', 'sort_order', 'is_active'];

    protected function casts(): array
    {
        return ['is_active' => 'boolean', 'sort_order' => 'integer'];
    }

    protected static function booted(): void
    {
        static::saved(fn () => self::forgetCache());
        static::deleted(fn () => self::forgetCache());
    }

    public static function forgetCache(): void
    {
        Cache::forget(self::CACHE_KEY);
    }

    /**
     * Every genre, ordered, as plain arrays (cached). Falls back to the built-in list before the migration has run.
     *
     * @return Collection<int, array{slug:string,name_en:string,name_bn:?string,is_active:bool,sort_order:int}>
     */
    public static function everything(): Collection
    {
        return Cache::remember(self::CACHE_KEY, 3600, function () {
            if (! Schema::hasTable('genres')) {
                $i = 0;

                return collect(self::DEFAULTS)->map(fn ($n, $slug) => ['slug' => $slug, 'name_en' => $n[0], 'name_bn' => $n[1], 'is_active' => true, 'sort_order' => ++$i])->values();
            }

            return self::query()->orderBy('sort_order')->orderBy('id')
                ->get(['slug', 'name_en', 'name_bn', 'is_active', 'sort_order'])
                ->map(fn ($g) => $g->only(['slug', 'name_en', 'name_bn', 'is_active', 'sort_order']))
                ->values();
        });
    }

    /** Active genres only. */
    public static function active(): Collection
    {
        return self::everything()->where('is_active', true)->values();
    }

    /** Display label: en | bn | both ("বাংলা (English)"). */
    public static function label(array $g, string $lang = 'en'): string
    {
        $bn = $g['name_bn'] ?? null;

        return match ($lang) {
            'bn'    => $bn ?: $g['name_en'],
            'both'  => $bn ? "{$bn} ({$g['name_en']})" : $g['name_en'],
            default => $g['name_en'],
        };
    }

    public static function nameFor(?string $slug, string $lang = 'en'): ?string
    {
        if (! $slug) {
            return null;
        }
        $g = self::everything()->firstWhere('slug', $slug);

        return $g ? self::label($g, $lang) : $slug;
    }

    /**
     * Validation rules for a genre field. The record's current value stays valid even if the genre has
     * since been switched off, so editing an old book never fails on a field the admin did not touch.
     */
    public static function rules(?string $current = null): array
    {
        $allowed = self::active()->pluck('slug')->all();
        if ($current) {
            $allowed[] = $current;
        }

        return ['nullable', 'string', 'max:100', Rule::in(array_unique($allowed))];
    }

    public function booksCount(): int
    {
        return Book::where('genre_category', $this->slug)->count();
    }
}
