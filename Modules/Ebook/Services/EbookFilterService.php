<?php

declare(strict_types=1);

namespace Modules\Ebook\Services;

use Illuminate\Database\Eloquent\Builder;

class EbookFilterService
{
    /**
     * Apply filters to ebook query.
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

        // File type filter
        if (!empty($filters['file_type'])) {
            $query->where('file_type', $filters['file_type']);
        }

        // Active filter
        if (isset($filters['active'])) {
            $query->where('is_active', (bool) $filters['active']);
        }

        // Search filter
        if (!empty($filters['search'])) {
            $query = $this->filterBySearch($query, $filters['search']);
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
     * Get available filter options for ebooks.
     *
     * @return array
     */
    public function getAvailableFilters(): array
    {
        return [
            'price_ranges' => [
                ['min' => 0, 'max' => 300, 'label' => 'Under ৳300'],
                ['min' => 300, 'max' => 600, 'label' => '৳300 - ৳600'],
                ['min' => 600, 'max' => 1000, 'label' => '৳600 - ৳1000'],
                ['min' => 1000, 'max' => PHP_INT_MAX, 'label' => 'Above ৳1000'],
            ],
            'discount_ranges' => [
                ['min' => 0, 'max' => 10, 'label' => '0-10%'],
                ['min' => 10, 'max' => 30, 'label' => '10-30%'],
                ['min' => 30, 'max' => 50, 'label' => '30-50%'],
                ['min' => 50, 'max' => PHP_INT_MAX, 'label' => '50%+'],
            ],
            'file_types' => ['pdf', 'epub', 'mobi', 'azw', 'azw3'],
        ];
    }
}
