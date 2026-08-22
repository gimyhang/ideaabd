<?php

namespace App\Services;

use App\Models\AuthorRoyalty;
use App\Models\Order;
use App\Models\User;
use App\Models\UserEbookLibrary;
use Illuminate\Support\Facades\Log;
use Modules\Author\Models\Author;
use Modules\Ebook\Models\Ebook;

class RoyaltyService
{
    /**
     * Process royalty distribution and library access for an order.
     */
    public static function processOrderRoyalties(Order $order): void
    {
        try {
            // 1. Check if the order is for an e-book or contains e-books
            $ebook = null;
            if ($order->book_id) {
                $ebook = Ebook::find($order->book_id);
            }

            if (!$ebook && !empty($order->admin_notes)) {
                // Check if any ebook is referenced in notes or items
                $ebook = Ebook::where('title', 'like', '%' . trim(explode('(', $order->customer_name ?? '')[0]) . '%')->first();
            }

            if (!$ebook) {
                return;
            }

            // 2. Grant access in UserEbookLibrary if user is logged in or by email/phone
            $buyerUserId = $order->user_id;
            if (!$buyerUserId && !empty($order->customer_phone)) {
                $buyerUser = User::where('phone', $order->customer_phone)
                    ->orWhere('email', $order->customer_email ?? '')
                    ->first();
                $buyerUserId = $buyerUser?->id;
            }

            if ($buyerUserId) {
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

            // 3. Increment E-Book Sales Count
            $qty = max(1, (int) $order->quantity);
            $ebook->increment('sales_count', $qty);

            // 4. Find Author
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

            // Prevent duplicate royalty credit for the same order & ebook
            $existingRoyalty = AuthorRoyalty::where('order_id', $order->id)
                ->where('ebook_id', $ebook->id)
                ->first();

            if ($existingRoyalty) {
                return;
            }

            // 5. Calculate 50% Royalty Share
            $salePrice = (float) $order->total_amount;
            $royaltyRate = (float) ($ebook->royalty_percentage ?: ($author->royalty_percentage ?: 50.00));
            $royaltyAmount = round(($salePrice * $royaltyRate) / 100, 2);
            $platformFee = round($salePrice - $royaltyAmount, 2);

            // 6. Record in author_royalties table
            AuthorRoyalty::create([
                'author_id'          => $author->id,
                'user_id'            => $author->user_id,
                'ebook_id'           => $ebook->id,
                'order_id'           => $order->id,
                'sale_price'         => $salePrice,
                'royalty_percentage' => $royaltyRate,
                'royalty_amount'     => $royaltyAmount,
                'platform_fee'       => $platformFee,
                'status'             => 'earned',
            ]);

            // 7. Credit Author's Wallet Balance
            $author->increment('wallet_balance', $royaltyAmount);

            Log::channel('audit')->info('Author Royalty Credited', [
                'order_id'       => $order->id,
                'author_id'      => $author->id,
                'ebook_id'       => $ebook->id,
                'royalty_amount' => $royaltyAmount,
                'platform_fee'   => $platformFee,
            ]);

        } catch (\Throwable $e) {
            Log::channel('json')->error('Royalty Processing Error', [
                'order_id' => $order->id,
                'error'    => $e->getMessage(),
            ]);
        }
    }
}
