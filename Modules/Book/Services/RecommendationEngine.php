<?php

declare(strict_types=1);

namespace Modules\Book\Services;

use Illuminate\Database\Eloquent\Collection;
use Modules\Book\Models\Book;
use Modules\Review\Models\Review;

class RecommendationEngine
{
    /**
     * Get books similar to the given book.
     *
     * @param Book $book
     * @param int $limit
     * @return Collection
     */
    public function getRelatedBooks(Book $book, int $limit = 6): Collection
    {
        return Book::where('id', '!=', $book->id)
            ->where('category_id', $book->category_id)
            ->where('is_active', true)
            ->orderBy('sales_count', 'desc')
            ->take($limit)
            ->get();
    }

    /**
     * Get books by same authors.
     *
     * @param Book $book
     * @param int $limit
     * @return Collection
     */
    public function getBooksByAuthor(Book $book, int $limit = 6): Collection
    {
        $authorIds = $book->authors()->pluck('author_id');

        return Book::where('id', '!=', $book->id)
            ->whereHas('authors', function ($q) use ($authorIds) {
                $q->whereIn('author_id', $authorIds);
            })
            ->where('is_active', true)
            ->orderBy('sales_count', 'desc')
            ->take($limit)
            ->get();
    }

    /**
     * Get personalized recommendations for user.
     *
     * @param $user
     * @param int $limit
     * @return Collection
     */
    public function getPersonalizedRecommendations($user, int $limit = 12): Collection
    {
        // Get user's order history
        $userBooks = $user->orders()
            ->with('items')
            ->get()
            ->pluck('items.*.book_id')
            ->flatten()
            ->unique();

        if ($userBooks->isEmpty()) {
            return $this->getTrendingBooks($limit);
        }

        // Get categories from user's orders
        $categories = Book::whereIn('id', $userBooks)->pluck('category_id')->unique();

        // Recommend from same categories
        return Book::whereIn('category_id', $categories)
            ->whereNotIn('id', $userBooks)
            ->where('is_active', true)
            ->orderBy('sales_count', 'desc')
            ->take($limit)
            ->get();
    }

    /**
     * Get trending/popular books.
     *
     * @param int $limit
     * @return Collection
     */
    public function getTrendingBooks(int $limit = 12): Collection
    {
        return Book::where('is_active', true)
            ->orderBy('sales_count', 'desc')
            ->take($limit)
            ->get();
    }

    /**
     * Get top-rated books.
     *
     * @param int $limit
     * @return Collection
     */
    public function getTopRatedBooks(int $limit = 12): Collection
    {
        return Book::where('is_active', true)
            ->withAvg('reviews', 'rating')
            ->having('reviews_avg_rating', '>=', 4)
            ->orderBy('reviews_avg_rating', 'desc')
            ->take($limit)
            ->get();
    }

    /**
     * Get books on discount.
     *
     * @param int $limit
     * @return Collection
     */
    public function getDiscountedBooks(int $limit = 12): Collection
    {
        return Book::where('is_active', true)
            ->whereRaw('discount_price < price')
            ->orderByRaw('((price - discount_price) / price * 100) DESC')
            ->take($limit)
            ->get();
    }

    /**
     * Get new releases.
     *
     * @param int $limit
     * @param int $days
     * @return Collection
     */
    public function getNewReleases(int $limit = 12, int $days = 30): Collection
    {
        return Book::where('is_active', true)
            ->where('created_at', '>=', now()->subDays($days))
            ->latest('created_at')
            ->take($limit)
            ->get();
    }

    /**
     * Get collaborative recommendations (users who bought X also bought Y).
     *
     * @param Book $book
     * @param int $limit
     * @return Collection
     */
    public function getCollaborativeRecommendations(Book $book, int $limit = 6): Collection
    {
        // Get users who bought this book
        $userIds = $book->orders()
            ->distinct()
            ->pluck('user_id');

        if ($userIds->isEmpty()) {
            return $this->getRelatedBooks($book, $limit);
        }

        // Get other books these users bought
        return Book::where('id', '!=', $book->id)
            ->whereHas('orders', function ($q) use ($userIds) {
                $q->whereIn('user_id', $userIds);
            })
            ->where('is_active', true)
            ->orderBy('sales_count', 'desc')
            ->take($limit)
            ->get();
    }

    /**
     * Get book recommendation score.
     *
     * @param Book $book
     * @param Book $targetBook
     * @return int
     */
    public function getRecommendationScore(Book $book, Book $targetBook): int
    {
        $score = 0;

        // Same category (25 points)
        if ($book->category_id === $targetBook->category_id) {
            $score += 25;
        }

        // Same author (40 points)
        $sameAuthors = $book->authors()
            ->whereIn('author_id', $targetBook->authors()->pluck('author_id'))
            ->count();
        $score += $sameAuthors * 40;

        // Same tags (20 points per tag)
        $sameTags = $book->tags()
            ->whereIn('tag_id', $targetBook->tags()->pluck('tag_id'))
            ->count();
        $score += $sameTags * 20;

        // Similar price (10 points)
        if (abs($book->discount_price - $targetBook->discount_price) < 500) {
            $score += 10;
        }

        // Rating similarity (15 points)
        $bookRating = $book->reviews()->avg('rating') ?? 0;
        $targetRating = $targetBook->reviews()->avg('rating') ?? 0;
        if (abs($bookRating - $targetRating) < 1) {
            $score += 15;
        }

        return $score;
    }
}
