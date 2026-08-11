<?php

declare(strict_types=1);

namespace Modules\KidsZone\Services;

use Illuminate\Support\Str;
use Modules\KidsZone\Models\KidsZone;

class KidsZoneService
{
    /**
     * Get all active kids zones.
     *
     * @return mixed
     */
    public function getActive()
    {
        return KidsZone::active()->orderBy('featured_position')->get();
    }

    /**
     * Get zone with books.
     *
     * @param string $slug
     * @return KidsZone|null
     */
    public function getBySlug(string $slug): ?KidsZone
    {
        return KidsZone::where('slug', $slug)->with('books')->first();
    }

    /**
     * Create a new zone.
     *
     * @param array $data
     * @return KidsZone
     */
    public function create(array $data): KidsZone
    {
        $data['slug'] = $data['slug'] ?? Str::slug($data['name']);
        return KidsZone::create($data);
    }

    /**
     * Update zone.
     *
     * @param KidsZone $zone
     * @param array $data
     * @return KidsZone
     */
    public function update(KidsZone $zone, array $data): KidsZone
    {
        if (isset($data['name']) && $data['name'] !== $zone->name) {
            $data['slug'] = Str::slug($data['name']);
        }
        $zone->update($data);
        return $zone;
    }

    /**
     * Add books to zone.
     *
     * @param KidsZone $zone
     * @param array $bookIds
     * @return void
     */
    public function addBooks(KidsZone $zone, array $bookIds): void
    {
        $zone->books()->attach($bookIds);
    }

    /**
     * Set featured books.
     *
     * @param KidsZone $zone
     * @param array $bookIds
     * @return void
     */
    public function setFeaturedBooks(KidsZone $zone, array $bookIds): void
    {
        $zone->books()->each(function ($book) {
            $book->pivot->update(['is_featured' => false]);
        });

        foreach ($bookIds as $bookId) {
            $zone->books()->where('book_id', $bookId)->update(['is_featured' => true]);
        }
    }

    /**
     * Get zone recommendations.
     *
     * @param string $ageGroup
     * @param int $limit
     * @return mixed
     */
    public function getRecommendations(string $ageGroup, int $limit = 12)
    {
        return KidsZone::where('age_group', $ageGroup)
            ->active()
            ->with(['featuredBooks' => function ($q) {
                $q->take($limit);
            }])
            ->get();
    }

    /**
     * Delete zone.
     *
     * @param KidsZone $zone
     * @return bool|null
     */
    public function delete(KidsZone $zone): ?bool
    {
        return $zone->delete();
    }
}
