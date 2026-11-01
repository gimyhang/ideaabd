<?php

namespace App\Services;

use App\Models\AuthorRoyalty;
use App\Models\Order;
use App\Models\User;
use App\Models\UserEbookLibrary;
use App\Support\PhoneNormalizer;
use App\Support\SchemaCache;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Modules\Author\Models\Author;
use Modules\Book\Models\Book;
use Modules\Ebook\Models\Ebook;

class RoyaltyService
{
    /**
     * Process royalty distribution and library access for an order.
     */
    public static function processOrderRoyalties(Order $order): void
    {
        try {
            // Only process royalties and library access when payment is confirmed (or order is 100% free)
            if ($order->payment_status !== 'paid' && (float) $order->total_amount > 0) {
                return;
            }

            $notes = (string) ($order->admin_notes ?? '');
            $ebooks = collect();

            // 1. Extract all E-Book IDs from order notes (supports single & multi-item cart orders)
            if (preg_match_all('/\[EBOOK_ID:(\d+)\]/', $notes, $matches) && !empty($matches[1])) {
                $ebookIds = array_values(array_unique(array_map('intval', $matches[1])));
                if (SchemaCache::has('ebooks')) {
                    $ebooks = Ebook::whereIn('id', $ebookIds)->get();
                }
            }

            if ($ebooks->isEmpty() && str_contains($notes, '[ডিজিটাল ই-বুক]') && SchemaCache::has('ebooks')) {
                if (preg_match('/অর্ডার আইটেম:\s*(.+?)\s*\[ডিজিটাল ই-বুক\]/u', $notes, $tm)) {
                    $ebookTitle = trim($tm[1]);
                    if ($ebookTitle !== '') {
                        $found = Ebook::where('title', $ebookTitle)->first()
                            ?: Ebook::where('title', 'like', '%' . $ebookTitle . '%')->first();
                        if ($found) {
                            $ebooks->push($found);
                        }
                    }
                }
                if ($ebooks->isEmpty() && $order->book_id) {
                    $found = Ebook::find($order->book_id);
                    if ($found) {
                        $ebooks->push($found);
                    }
                }
            }

            // 2. Resolve buyer user ID safely (never matching empty email)
            $buyerUserId = $order->user_id;
            if (!$buyerUserId && !empty($order->customer_phone)) {
                $phoneNorm = PhoneNormalizer::normalize((string) $order->customer_phone);
                $phoneVariants = $phoneNorm['query_phones'] ?? [trim((string) $order->customer_phone)];

                $buyerUser = User::where(function ($q) use ($phoneVariants, $order) {
                    $q->whereIn('phone', $phoneVariants);
                    if (!empty($order->customer_email)) {
                        $q->orWhere('email', trim((string) $order->customer_email));
                    }
                })->first();

                $buyerUserId = $buyerUser?->id;
            }

            // 3. Process each E-Book in the order
            if ($ebooks->isNotEmpty()) {
                $itemCount = max(1, $ebooks->count());
                $perEbookSalePrice = round(((float) $order->total_amount) / $itemCount, 2);

                foreach ($ebooks as $ebook) {
                    static::grantEbookAccessAndRoyalty($order, $ebook, $buyerUserId, $perEbookSalePrice);
                }

                Cache::forget('admin_dash_getRoyaltiesPipeline');
                return;
            }

            // 4. Physical Book Order: Increment sales_count when payment is confirmed
            if ($order->book_id && SchemaCache::has('books')) {
                $cacheKey = 'order_book_sales_counted_' . $order->id;
                if (Cache::add($cacheKey, true, now()->addDays(30))) {
                    $qty = max(1, (int) $order->quantity);
                    Book::where('id', $order->book_id)->increment('sales_count', $qty);
                }
            }

        } catch (\Throwable $e) {
            Log::channel('json')->error('Royalty Processing Error', [
                'order_id' => $order->id,
                'error'    => $e->getMessage(),
            ]);
        }
    }

    /**
     * Grant library access and record author royalty for a single E-Book in an order.
     */
    protected static function grantEbookAccessAndRoyalty(Order $order, Ebook $ebook, ?int $buyerUserId, float $salePrice): void
    {
        if ($buyerUserId && SchemaCache::has('user_ebook_library')) {
            UserEbookLibrary::updateOrCreate(
                [
                    'user_id'  => $buyerUserId,
                    'ebook_id' => $ebook->id,
                ],
                [
                    'order_id'    => $order->id,
                    'access_type' => 'purchased',
                    'is_active'   => true,
                ]
            );
        }

        // Prevent duplicate royalty credit & duplicate sales_count increment for the same order & ebook
        $existingRoyalty = AuthorRoyalty::where('order_id', $order->id)
            ->where('ebook_id', $ebook->id)
            ->first();

        if ($existingRoyalty) {
            return;
        }

        // Increment E-Book Sales Count
        $qty = max(1, (int) $order->quantity);
        $ebook->increment('sales_count', $qty);

        // Find Author
        $author = null;
        if ($ebook->author_id) {
            $author = Author::find($ebook->author_id);
        }
        if (!$author && $ebook->author_user_id) {
            $authorUser = User::find($ebook->author_user_id);
            $author = $authorUser?->getAuthorRecord();
        }
        if (!$author && !empty($ebook->author_link_id)) {
            $author = Author::find($ebook->author_link_id);
        }

        if (!$author) {
            return;
        }

        // Ensure Author has user_id if author is a registered user
        if (!$author->user_id && $ebook->author_user_id) {
            $author->update(['user_id' => $ebook->author_user_id]);
        }

        // Calculate Royalty Share (Checks if ebook is Royalty-Free)
        $isRoyaltyFree = $ebook->isRoyaltyFree();

        if ($isRoyaltyFree) {
            $royaltyRate = 0.00;
            $royaltyAmount = 0.00;
            $platformFee = $salePrice;
        } else {
            $royaltyRate = $ebook->royalty_percentage !== null
                ? (float) $ebook->royalty_percentage
                : (float) ($author->royalty_percentage ?: 50.00);
            $royaltyAmount = round(($salePrice * $royaltyRate) / 100, 2);
            $platformFee = round($salePrice - $royaltyAmount, 2);
        }

        // Record in author_royalties table
        AuthorRoyalty::create([
            'author_id'          => $author->id,
            'user_id'            => $author->user_id,
            'ebook_id'           => $ebook->id,
            'order_id'           => $order->id,
            'sale_price'         => $salePrice,
            'royalty_percentage' => $royaltyRate,
            'royalty_amount'     => $royaltyAmount,
            'platform_fee'       => $platformFee,
            'status'             => $royaltyAmount > 0 ? 'earned' : 'royalty_free',
        ]);

        // Credit Author's Wallet Balance if royalty amount > 0
        if ($royaltyAmount > 0) {
            $author->increment('wallet_balance', $royaltyAmount);
        }

        Log::channel('audit')->info('Author Royalty Credited', [
            'order_id'       => $order->id,
            'author_id'      => $author->id,
            'ebook_id'       => $ebook->id,
            'royalty_amount' => $royaltyAmount,
            'platform_fee'   => $platformFee,
        ]);
    }
}
