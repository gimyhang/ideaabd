<?php

namespace App\Services;

use Illuminate\Support\Str;
use Modules\Book\Models\Book;

class IdeaInvoiceItemService
{
    /**
     * Item type definitions with metadata.
     */
    public const TYPE_BOOK_HARDCOVER = 'Book (Hardcover)';
    public const TYPE_BOOK_PAPERBACK = 'Book (Paperback)';
    public const TYPE_BOOK_STANDARD  = 'Book (Standard)';
    public const TYPE_STATIONERY     = 'Stationery';
    public const TYPE_PRINTING       = 'Printing & Binding';
    public const TYPE_PAPER          = 'Paper / Raw Materials';
    public const TYPE_PRODUCT        = 'Product';
    public const TYPE_SERVICE        = 'Service';
    public const TYPE_OTHER          = 'Other';

    /**
     * Standard units list.
     */
    public const UNITS = [
        'Copy',
        'Pcs',
        'Ream',
        'Forma',
        'Box',
        'Set',
        'Pack',
        'Pad',
        'Book',
        'Kg',
        'Sqft',
        'Roll',
        'Bag',
        'Item',
    ];

    /**
     * Get grouped list of item types with configuration.
     */
    public static function getItemTypes(): array
    {
        return [
            'Books' => [
                self::TYPE_BOOK_HARDCOVER => ['name' => 'Book (Hardcover)', 'unit' => 'Copy', 'is_book' => true, 'icon' => 'fa-book'],
                self::TYPE_BOOK_PAPERBACK => ['name' => 'Book (Paperback)', 'unit' => 'Copy', 'is_book' => true, 'icon' => 'fa-book-open'],
                self::TYPE_BOOK_STANDARD  => ['name' => 'Book (Standard)',  'unit' => 'Copy', 'is_book' => true, 'icon' => 'fa-book-bookmark'],
            ],
            'Stationery & Supplies' => [
                self::TYPE_STATIONERY     => ['name' => 'Stationery & Materials', 'unit' => 'Pcs', 'is_book' => false, 'icon' => 'fa-pen-ruler'],
            ],
            'Printing & Press' => [
                self::TYPE_PRINTING       => ['name' => 'Printing & Binding',     'unit' => 'Copy', 'is_book' => false, 'icon' => 'fa-print'],
                self::TYPE_PAPER          => ['name' => 'Paper & Materials',      'unit' => 'Ream', 'is_book' => false, 'icon' => 'fa-box-archive'],
            ],
            'Products & Services' => [
                self::TYPE_PRODUCT        => ['name' => 'General Product',        'unit' => 'Pcs',  'is_book' => false, 'icon' => 'fa-tag'],
                self::TYPE_SERVICE        => ['name' => 'Service & Support',      'unit' => 'Item', 'is_book' => false, 'icon' => 'fa-screwdriver-wrench'],
                self::TYPE_OTHER          => ['name' => 'Other Items',            'unit' => 'Item', 'is_book' => false, 'icon' => 'fa-receipt'],
            ],
        ];
    }

    /**
     * Determine if an item is a book in the Bookshop inventory (/admin/books).
     */
    public static function isBookItem(?string $itemType, string $salesCategory = 'books'): bool
    {
        if (!empty($itemType)) {
            $lower = mb_strtolower(trim($itemType));

            // Excluded non-book types
            $nonBookKeywords = [
                'stationery', 'supply', 'material', 'equipment',
                'printing', 'binding', 'press',
                'paper', 'ream', 'raw',
                'service', 'maintenance',
                'product', 'merchandise',
                'other', 'bill', 'custom',
                'receipt', 'card', 'challan', 'memo', 'pad', 'voucher', 'banner', 'leaflet', 'brochure'
            ];

            foreach ($nonBookKeywords as $keyword) {
                if (str_contains($lower, $keyword)) {
                    return false;
                }
            }

            // Recognized book types
            if (str_contains($lower, 'book (hardcover)') || str_contains($lower, 'book (paperback)') || str_contains($lower, 'book (standard)') || $lower === 'book' || $lower === 'বই') {
                return true;
            }
        }

        return $salesCategory === 'books';
    }

    /**
     * Get default unit for item type.
     */
    public static function getDefaultUnit(?string $itemType, string $salesCategory = 'books'): string
    {
        if (empty($itemType)) {
            return $salesCategory === 'books' ? 'Copy' : 'Pcs';
        }

        $lower = mb_strtolower(trim($itemType));
        if (str_contains($lower, 'paper') || str_contains($lower, 'ream')) {
            return 'Ream';
        }
        if (str_contains($lower, 'book')) {
            return 'Copy';
        }
        if (str_contains($lower, 'service')) {
            return 'Item';
        }

        return 'Pcs';
    }

    /**
     * Quick Presets for fast 1-click addition.
     */
    public static function getPresets(): array
    {
        return [
            'stationery' => [
                'Notebooks & Diaries' => [
                    ['title' => 'Executive Hardbound Diary', 'spec' => 'Premium Gold Foil, 192 Pages', 'type' => self::TYPE_STATIONERY, 'unit' => 'Pcs', 'price' => 350, 'reg' => 450],
                    ['title' => 'Spiral Executive Notebook', 'spec' => '80 GSM Premium Ruled (160 Pages)', 'type' => self::TYPE_STATIONERY, 'unit' => 'Pcs', 'price' => 150, 'reg' => 180],
                    ['title' => 'Exercise Notebook / Khata', 'spec' => '120 Pages, Laminated Cover', 'type' => self::TYPE_STATIONERY, 'unit' => 'Pcs', 'price' => 65, 'reg' => 80],
                    ['title' => 'Official Ledger / Register Book', 'spec' => '200 Pages Hardbound Cloth Binding', 'type' => self::TYPE_STATIONERY, 'unit' => 'Pcs', 'price' => 240, 'reg' => 290],
                    ['title' => 'Pocket Memo Pad', 'spec' => 'Top Spiral Bound (80 Pages)', 'type' => self::TYPE_STATIONERY, 'unit' => 'Pcs', 'price' => 45, 'reg' => 60],
                ],
                'Writing & Office Tools' => [
                    ['title' => 'Smooth Ballpoint Pen Box', 'spec' => '0.7mm Tip (10 Pcs Pack)', 'type' => self::TYPE_STATIONERY, 'unit' => 'Box', 'price' => 120, 'reg' => 150],
                    ['title' => 'Gel Pen Set (5 Colors)', 'spec' => '0.5mm Quick-Dry Japanese Ink', 'type' => self::TYPE_STATIONERY, 'unit' => 'Set', 'price' => 180, 'reg' => 220],
                    ['title' => 'Pastel Highlighter Set (6 Colors)', 'spec' => 'Non-Smudge Pastel Chisel Tip', 'type' => self::TYPE_STATIONERY, 'unit' => 'Set', 'price' => 240, 'reg' => 290],
                    ['title' => 'Whiteboard Marker & Duster Kit', 'spec' => '4 Colors + Magnetic Duster', 'type' => self::TYPE_STATIONERY, 'unit' => 'Set', 'price' => 175, 'reg' => 215],
                    ['title' => 'Sticky Notes Pad (3x3 Inch)', 'spec' => '100 Neon Sheets Multi-color', 'type' => self::TYPE_STATIONERY, 'unit' => 'Pad', 'price' => 60, 'reg' => 80],
                ],
                'Filing & Paper Reams' => [
                    ['title' => 'Premium A4 Offset Paper Ream', 'spec' => '80 GSM, 500 Sheets High Brightness', 'type' => self::TYPE_PAPER, 'unit' => 'Ream', 'price' => 480, 'reg' => 550],
                    ['title' => 'Heavy Duty Ring Binder Box File', 'spec' => 'Standard Office Lever Arch File', 'type' => self::TYPE_STATIONERY, 'unit' => 'Pcs', 'price' => 120, 'reg' => 150],
                    ['title' => 'Clear Button Document Pouch (A4)', 'spec' => 'Transparent Waterproof Poly Pouch', 'type' => self::TYPE_STATIONERY, 'unit' => 'Pcs', 'price' => 35, 'reg' => 45],
                    ['title' => 'Official Mailing Envelopes (Pack of 50)', 'spec' => '10x4.5 Inch, 100 GSM Self-Adhesive', 'type' => self::TYPE_STATIONERY, 'unit' => 'Pack', 'price' => 140, 'reg' => 175],
                ],
            ],
            'printing' => [
                'Books & Publications' => [
                    ['title' => 'Custom Book Printing & Binding (Demy)', 'spec' => 'Demy 5.5x8.5", 80 GSM Offset, 4C Cover, Perfect Bound', 'type' => self::TYPE_PRINTING, 'unit' => 'Copy', 'price' => 140, 'reg' => 160],
                    ['title' => 'Premium Hardcover Book Printing', 'spec' => 'Royal 6.25x9.5", 100 GSM, Embossed Gold Foil', 'type' => self::TYPE_PRINTING, 'unit' => 'Copy', 'price' => 240, 'reg' => 280],
                    ['title' => 'Souvenir / Magazine Printing (A4)', 'spec' => 'A4, 150 GSM Art Paper Cover, 80 GSM Inner', 'type' => self::TYPE_PRINTING, 'unit' => 'Copy', 'price' => 95, 'reg' => 120],
                    ['title' => 'Annual Report & Corporate Profile', 'spec' => 'A4, 150 GSM Art Paper, Spiral / Wire-O Binding', 'type' => self::TYPE_PRINTING, 'unit' => 'Copy', 'price' => 165, 'reg' => 195],
                ],
                'Commercial & Office Printing' => [
                    ['title' => 'Cash Memo / Money Receipt Book', 'spec' => '2-Part / 3-Part NCR Carbonless, 100 Sheets Serialized', 'type' => self::TYPE_PRINTING, 'unit' => 'Book', 'price' => 130, 'reg' => 160],
                    ['title' => 'Delivery Challan Book (3-Part NCR)', 'spec' => '3-Part NCR Carbonless, Hard Board Back', 'type' => self::TYPE_PRINTING, 'unit' => 'Book', 'price' => 145, 'reg' => 175],
                    ['title' => 'Executive Letterhead Pad (100 GSM)', 'spec' => '100 GSM Premium Paper, 4C Print, 50 Sheets Pad', 'type' => self::TYPE_PRINTING, 'unit' => 'Pad', 'price' => 190, 'reg' => 230],
                    ['title' => 'Business Cards Box (100 Pcs)', 'spec' => '300 GSM Art Card, 2-Sided Matt + Spot UV', 'type' => self::TYPE_PRINTING, 'unit' => 'Box', 'price' => 380, 'reg' => 480],
                    ['title' => 'Official Printed Envelopes (Per 1,000)', 'spec' => '10x4.5 Inch, 100 GSM Offset, 4C Print', 'type' => self::TYPE_PRINTING, 'unit' => 'Pack', 'price' => 2400, 'reg' => 2800],
                ],
                'Marketing & Display Materials' => [
                    ['title' => 'Promotional Flyers / Leaflets (Per 1,000)', 'spec' => 'A4 / A5, 120 GSM Art Paper, 2-Sided 4C', 'type' => self::TYPE_PRINTING, 'unit' => 'Pack', 'price' => 2800, 'reg' => 3300],
                    ['title' => 'Folded Product Brochure (3-Fold)', 'spec' => '170 GSM Glossy Art Paper, Full Color', 'type' => self::TYPE_PRINTING, 'unit' => 'Copy', 'price' => 28, 'reg' => 38],
                    ['title' => 'Desk Calendar Printing', 'spec' => '12 Sheets Matt Laminated, Hard Stand Board', 'type' => self::TYPE_PRINTING, 'unit' => 'Pcs', 'price' => 135, 'reg' => 165],
                    ['title' => 'Digital PVC Banner / Festoon', 'spec' => 'Heavy Duty Digital PVC Flex (Per Sqft)', 'type' => self::TYPE_SERVICE, 'unit' => 'Sqft', 'price' => 28, 'reg' => 38],
                ],
            ],
        ];
    }

    /**
     * Process and sanitize invoice items before saving to database.
     */
    public static function processItems(array $rawItems, string $salesCategory = 'books', bool $autoCreateBooks = false): array
    {
        $processed = [];
        $subtotal = 0.0;

        foreach ($rawItems as $item) {
            $title = trim((string)($item['title'] ?? ''));
            if (empty($title)) {
                continue;
            }

            $qty = (float)($item['quantity'] ?? 1);
            $qty = max(0.01, $qty);

            $price = (float)($item['price'] ?? 0);
            $regularPrice = isset($item['regular_price']) && is_numeric($item['regular_price']) && (float)$item['regular_price'] > 0
                ? (float)$item['regular_price']
                : $price;

            $discPct = isset($item['discount_percent']) && is_numeric($item['discount_percent'])
                ? (float)$item['discount_percent']
                : 0.0;

            if ($discPct == 0 && $regularPrice > $price && $regularPrice > 0) {
                $discPct = round((($regularPrice - $price) / $regularPrice) * 100, 2);
            }

            $lineTotal = $qty * $price;
            $subtotal += $lineTotal;

            $itemType = $item['item_type'] ?? null;
            $isBook = self::isBookItem($itemType, $salesCategory);
            $bookId = !empty($item['book_id']) ? (int)$item['book_id'] : null;

            if ($isBook) {
                // If not explicitly linked, check if an existing book matches by exact title
                if (!$bookId && !empty($title)) {
                    $existingBook = Book::where('title', $title)->first();
                    if ($existingBook) {
                        $bookId = $existingBook->id;
                    }
                }
            } else {
                // Non-book items strictly bypass books table
                $bookId = null;
            }

            $unit = !empty($item['unit']) ? trim((string)$item['unit']) : self::getDefaultUnit($itemType, $salesCategory);

            $processed[] = [
                'title'            => $title,
                'author_name'      => !empty($item['author_name']) ? trim((string)$item['author_name']) : null,
                'item_type'        => $itemType ?: ($isBook ? self::TYPE_BOOK_PAPERBACK : self::TYPE_PRODUCT),
                'unit'             => $unit,
                'book_id'          => $bookId,
                'quantity'         => $qty,
                'regular_price'    => $regularPrice,
                'discount_percent' => $discPct,
                'unit_price'       => $price,
                'subtotal'         => $lineTotal,
            ];
        }

        return [
            'items'    => $processed,
            'subtotal' => $subtotal,
        ];
    }
}
