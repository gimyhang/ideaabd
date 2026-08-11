<?php

declare(strict_types=1);

namespace Modules\Book\Services;

use Modules\Book\Models\Book;
use Modules\Book\Models\Wishlist;

class WishlistService
{
    /**
     * Add book to wishlist.
     *
     * @param $userId
     * @param int $bookId
     * @param string|null $notes
     * @return Wishlist
     */
    public function add($userId, int $bookId, ?string $notes = null): Wishlist
    {
        return Wishlist::firstOrCreate(
            ['user_id' => $userId, 'book_id' => $bookId],
            ['notes' => $notes]
        );
    }

    /**
     * Remove book from wishlist.
     *
     * @param $userId
     * @param int $bookId
     * @return bool
     */
    public function remove($userId, int $bookId): bool
    {
        return Wishlist::where('user_id', $userId)
            ->where('book_id', $bookId)
            ->delete() > 0;
    }

    /**
     * Get user's wishlist.
     *
     * @param $userId
     * @param int $limit
     * @return mixed
     */
    public function getWishlist($userId, int $limit = 20)
    {
        return Wishlist::where('user_id', $userId)
            ->active()
            ->byPriority()
            ->with('book')
            ->paginate($limit);
    }

    /**
     * Update wishlist item.
     *
     * @param $userId
     * @param int $bookId
     * @param array $data
     * @return Wishlist|null
     */
    public function update($userId, int $bookId, array $data): ?Wishlist
    {
        $wishlist = Wishlist::where('user_id', $userId)
            ->where('book_id', $bookId)
            ->first();

        if ($wishlist) {
            $wishlist->update($data);
            return $wishlist;
        }

        return null;
    }

    /**
     * Get wishlist statistics.
     *
     * @param $userId
     * @return array
     */
    public function getStatistics($userId): array
    {
        $wishlist = Wishlist::where('user_id', $userId)->get();
        $totalPrice = $wishlist->sum(fn($item) => $item->book->discount_price);
        $totalSavings = $wishlist->sum(fn($item) => $item->book->price - $item->book->discount_price);

        return [
            'total_items' => $wishlist->count(),
            'total_price' => $totalPrice,
            'total_savings' => $totalSavings,
            'average_price' => $wishlist->count() > 0 ? $totalPrice / $wishlist->count() : 0,
        ];
    }

    /**
     * Check if book is in wishlist.
     *
     * @param $userId
     * @param int $bookId
     * @return bool
     */
    public function isInWishlist($userId, int $bookId): bool
    {
        return Wishlist::where('user_id', $userId)
            ->where('book_id', $bookId)
            ->exists();
    }

    /**
     * Clear wishlist.
     *
     * @param $userId
     * @return bool
     */
    public function clear($userId): bool
    {
        return Wishlist::where('user_id', $userId)->delete() > 0;
    }

    /**
     * Export wishlist as PDF or CSV.
     *
     * @param $userId
     * @param string $format
     * @return string
     */
    public function export($userId, string $format = 'pdf'): string
    {
        $wishlist = Wishlist::where('user_id', $userId)->with('book')->get();

        if ($format === 'csv') {
            return $this->exportAsCSV($wishlist);
        }

        return $this->exportAsPDF($wishlist);
    }

    /**
     * Export wishlist as CSV.
     *
     * @param $wishlist
     * @return string
     */
    private function exportAsCSV($wishlist): string
    {
        $csv = "Book Title,Author,Price,Discount Price,Category\n";

        foreach ($wishlist as $item) {
            $csv .= "\"{$item->book->title}\",\"{$item->book->authors()->pluck('name')->join(', ')}\",{$item->book->price},{$item->book->discount_price},\"{$item->book->category->name}\"\n";
        }

        return $csv;
    }

    /**
     * Export wishlist as PDF.
     *
     * @param $wishlist
     * @return string
     */
    private function exportAsPDF($wishlist): string
    {
        // This would typically use a package like Dompdf or TCPDF
        return 'PDF export functionality';
    }
}
