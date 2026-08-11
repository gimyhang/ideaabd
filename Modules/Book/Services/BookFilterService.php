<?php

declare(strict_types=1);

namespace Modules\Book\Services;

use Illuminate\Database\Eloquent\Builder;
use Modules\Book\Models\Book;

class BookFilterService
{
    /**
     * Apply filters to book query.
     *
     * @param Builder $query
     * @param array $filters
     * @return Builder
     */
    public function applyFilters(Builder $query, array $filters): Builder
    {
        // Price range filter
        if (isset($filters['price_min']) || isset($filters['price_max'])) {
            $query = $this->filterByPrice($query, $filters);
        }

        // Discount range filter
        if (isset($filters['discount_min']) || isset($filters['discount_max'])) {
            $query = $this->filterByDiscount($query, $filters);
        }

        // Category filter
        if (!empty($filters['categories'])) {
            $query->whereIn('category_id', (array) $filters['categories']);
        }

        // Author filter
        if (!empty($filters['authors'])) {
            $query->whereHas('authors', function ($q) use ($filters) {
                $q->whereIn('author_id', (array) $filters['authors']);
            });
        }

        // Publisher/Vendor filter
        if (!empty($filters['vendors'])) {
            $query->whereIn('vendor_id', (array) $filters['vendors']);
        }

        // Tag filter
        if (!empty($filters['tags'])) {
            $query->whereHas('tags', function ($q) use ($filters) {
                $q->whereIn('tag_id', (array) $filters['tags']);
            });
        }

        // Format filter (printed, ebook, both)
        if (!empty($filters['format'])) {
            $query->where('format', $filters['format']);
        }

        // Stock availability filter
        if (isset($filters['in_stock']) && $filters['in_stock'] === true) {
            $query->where('stock_quantity', '>', 0);
        }

        // Active filter
        if (isset($filters['active'])) {
            $query->where('is_active', (bool) $filters['active']);
        }

        // ISBN filter
        if (!empty($filters['isbn'])) {
            $query->where('isbn', $filters['isbn']);
        }

        // Search filter
        if (!empty($filters['search'])) {
            $query = $this->filterBySearch($query, $filters['search']);
        }

        // Rating filter
        if (isset($filters['rating_min'])) {
            $query->whereHas('reviews', function ($q) use ($filters) {
                $q->where('rating', '>=', $filters['rating_min']);
            }, '>=', 1);
        }

        return $query;
    }

    /**
     * Filter by price range.
     *
     * @param Builder $query
     * @param array $filters
     * @return Builder
     */
    private function filterByPrice(Builder $query, array $filters): Builder
    {
        if (isset($filters['price_min'])) {
            $query->where('discount_price', '>=', $filters['price_min']);
        }

        if (isset($filters['price_max'])) {
            $query->where('discount_price', '<=', $filters['price_max']);
        }

        return $query;
    }

    /**
     * Filter by discount percentage.
     *
     * @param Builder $query
     * @param array $filters
     * @return Builder
     */
    private function filterByDiscount(Builder $query, array $filters): Builder
    {
        if (isset($filters['discount_min'])) {
            $query->whereRaw('((price - discount_price) / price * 100) >= ?', [$filters['discount_min']]);
        }

        if (isset($filters['discount_max'])) {
            $query->whereRaw('((price - discount_price) / price * 100) <= ?', [$filters['discount_max']]);
        }

        return $query;
    }

    /**
     * Filter by search query.
     *
     * @param Builder $query
     * @param string $search
     * @return Builder
     */
    private function filterBySearch(Builder $query, string $search): Builder
    {
        return $query->where(function ($q) use ($search) {
            $q->where('title', 'like', "%{$search}%")
                ->orWhere('description', 'like', "%{$search}%")
                ->orWhere('isbn', 'like', "%{$search}%")
                ->orWhereHas('authors', function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%");
                });
        });
    }

    /**
     * Get available filter options.
     *
     * @return array
     */
    public function getAvailableFilters(): array
    {
        return [
            'price_ranges' => [
                ['min' => 0, 'max' => 500, 'label' => 'Under ৳500'],
                ['min' => 500, 'max' => 1000, 'label' => '৳500 - ৳1000'],
                ['min' => 1000, 'max' => 2000, 'label' => '৳1000 - ৳2000'],
                ['min' => 2000, 'max' => 5000, 'label' => '৳2000 - ৳5000'],
                ['min' => 5000, 'max' => PHP_INT_MAX, 'label' => 'Above ৳5000'],
            ],
            'discount_ranges' => [
                ['min' => 0, 'max' => 10, 'label' => '0-10%'],
                ['min' => 10, 'max' => 20, 'label' => '10-20%'],
                ['min' => 20, 'max' => 50, 'label' => '20-50%'],
                ['min' => 50, 'max' => PHP_INT_MAX, 'label' => '50%+'],
            ],
            'formats' => ['printed', 'ebook', 'both'],
            'ratings' => [
                ['min' => 4.5, 'label' => '4.5+ Stars'],
                ['min' => 4.0, 'label' => '4+ Stars'],
                ['min' => 3.5, 'label' => '3.5+ Stars'],
            ],
        ];
    }

    /**
     * Build filter query string.
     *
     * @param array $filters
     * @return string
     */
    public function buildFilterQuery(array $filters): string
    {
        return http_build_query(array_filter($filters));
    }
}
