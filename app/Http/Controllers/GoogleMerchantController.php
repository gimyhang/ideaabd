<?php

namespace App\Http\Controllers;

use Illuminate\Http\Response;
use Illuminate\Support\Str;
use Modules\Book\Models\Book;
use Modules\Ebook\Models\Ebook;
use Modules\Publisher\Models\Publisher;

class GoogleMerchantController extends Controller
{
    /**
     * Get clean production base URL.
     */
    protected function getBaseUrl(): string
    {
        $baseUrl = config('app.url', 'https://www.ideaabd.com');
        if (str_contains($baseUrl, 'localhost') || str_contains($baseUrl, '127.0.0.1')) {
            $baseUrl = 'https://www.ideaabd.com';
        }
        return rtrim($baseUrl, '/');
    }

    /**
     * Google Merchant Center Product Feed (RSS 2.0 / Atom XML Format).
     * Standard Google Shopping feed specification.
     */
    public function feedXml(): Response
    {
        $baseUrl = $this->getBaseUrl();
        $siteName = \App\Support\SiteSetting::name() ?: 'আইডিয়া প্রকাশন';
        $siteDesc = \App\Support\SiteSetting::tagline() ?: 'অনলাইন বই ও প্রকাশনা প্ল্যাটফর্ম';
        $defaultLogo = url(asset('images/logo.png'));

        $books = Book::where('is_active', true)
            ->where(function($q) {
                $q->whereNull('mod_status')->orWhere('mod_status', 'approved');
            })
            ->with(['authors', 'category', 'publisher'])
            ->latest('id')
            ->get();

        $ebooks = [];
        try {
            if (\Illuminate\Support\Facades\Schema::hasTable('ebooks')) {
                $ebooks = Ebook::where('is_active', true)->with(['category'])->latest('id')->get();
            }
        } catch (\Throwable $e) {}

        $xml = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
        $xml .= '<rss xmlns:g="http://base.google.com/ns/1.0" version="2.0">' . "\n";
        $xml .= "  <channel>\n";
        $xml .= "    <title>" . htmlspecialchars($siteName . ' — Google Merchant Product Feed', ENT_XML1, 'UTF-8') . "</title>\n";
        $xml .= "    <link>{$baseUrl}</link>\n";
        $xml .= "    <description>" . htmlspecialchars($siteDesc, ENT_XML1, 'UTF-8') . "</description>\n";

        // 1. Physical Print Books
        foreach ($books as $b) {
            $id = 'BOOK-' . $b->id;
            $slug = $b->slug ?: $b->id;
            $link = $baseUrl . '/books/' . $slug;

            $authorName = $b->authors->isNotEmpty() ? $b->authors->pluck('name')->join(', ') : ($b->author_name ?: 'আইডিয়া প্রকাশন');
            $title = $b->title . ' — ' . $authorName;

            $rawDesc = strip_tags((string)($b->summary ?: ($b->description ?: $title)));
            $desc = Str::limit($rawDesc, 4500);

            // Cover Image
            $imageLink = $defaultLogo;
            if (!empty($b->cover_image)) {
                $imageLink = str_starts_with((string)$b->cover_image, 'http') ? $b->cover_image : $baseUrl . '/storage/' . ltrim((string)$b->cover_image, '/');
            }

            // Price & Sale Price
            $regPrice = max(1, (float)($b->price > 0 ? $b->price : ($b->discount_price > 0 ? $b->discount_price : 100)));
            $salePrice = ($b->discount_price > 0 && $b->discount_price < $regPrice) ? (float)$b->discount_price : null;

            $priceStr = number_format($regPrice, 2, '.', '') . ' BDT';
            $salePriceStr = $salePrice ? (number_format($salePrice, 2, '.', '') . ' BDT') : null;

            // Stock Availability
            $stockStatus = strtolower((string)$b->stock_status);
            $availability = ($stockStatus === 'out_of_stock' || (is_numeric($b->stock_quantity) && $b->stock_quantity <= 0)) ? 'out_of_stock' : 'in_stock';
            if ($stockStatus === 'pre_order') {
                $availability = 'preorder';
            }

            $brand = $b->publisher?->name ?: ($b->author_name ?: 'আইডিয়া প্রকাশন');
            $categoryName = $b->category?->name ?: 'বাংলা বই';
            $isbn = trim((string)$b->isbn);

            $xml .= "    <item>\n";
            $xml .= "      <g:id>" . htmlspecialchars($id, ENT_XML1, 'UTF-8') . "</g:id>\n";
            $xml .= "      <g:title>" . htmlspecialchars($title, ENT_XML1, 'UTF-8') . "</g:title>\n";
            $xml .= "      <g:description>" . htmlspecialchars($desc, ENT_XML1, 'UTF-8') . "</g:description>\n";
            $xml .= "      <g:link>" . htmlspecialchars($link, ENT_XML1, 'UTF-8') . "</g:link>\n";
            $xml .= "      <g:image_link>" . htmlspecialchars($imageLink, ENT_XML1, 'UTF-8') . "</g:image_link>\n";
            $xml .= "      <g:availability>{$availability}</g:availability>\n";
            $xml .= "      <g:price>{$priceStr}</g:price>\n";
            if ($salePriceStr) {
                $xml .= "      <g:sale_price>{$salePriceStr}</g:sale_price>\n";
            }
            $xml .= "      <g:google_product_category>Media &gt; Books &gt; Print Books</g:google_product_category>\n";
            $xml .= "      <g:product_type>" . htmlspecialchars("Books > {$categoryName}", ENT_XML1, 'UTF-8') . "</g:product_type>\n";
            $xml .= "      <g:brand>" . htmlspecialchars($brand, ENT_XML1, 'UTF-8') . "</g:brand>\n";
            $xml .= "      <g:condition>new</g:condition>\n";

            if (!empty($isbn)) {
                $xml .= "      <g:isbn>" . htmlspecialchars($isbn, ENT_XML1, 'UTF-8') . "</g:isbn>\n";
                $xml .= "      <g:identifier_exists>yes</g:identifier_exists>\n";
            } else {
                $xml .= "      <g:identifier_exists>no</g:identifier_exists>\n";
            }

            // Shipping standard for Bangladesh
            $xml .= "      <g:shipping>\n";
            $xml .= "        <g:country>BD</g:country>\n";
            $xml .= "        <g:service>Standard Delivery</g:service>\n";
            $xml .= "        <g:price>60.00 BDT</g:price>\n";
            $xml .= "      </g:shipping>\n";

            $xml .= "    </item>\n";
        }

        // 2. E-books
        foreach ($ebooks as $eb) {
            $id = 'EBOOK-' . $eb->id;
            $slug = $eb->slug ?: $eb->id;
            $link = $baseUrl . '/ebooks/' . $slug;
            $title = $eb->title . ' (ই-বুক)';
            $desc = Str::limit(strip_tags((string)($eb->description ?: $title)), 4500);

            $imageLink = $defaultLogo;
            if (!empty($eb->cover_image)) {
                $imageLink = str_starts_with((string)$eb->cover_image, 'http') ? $eb->cover_image : $baseUrl . '/storage/' . ltrim((string)$eb->cover_image, '/');
            }

            $price = max(0, (float)($eb->price > 0 ? $eb->price : 0));
            $priceStr = number_format($price, 2, '.', '') . ' BDT';

            $xml .= "    <item>\n";
            $xml .= "      <g:id>" . htmlspecialchars($id, ENT_XML1, 'UTF-8') . "</g:id>\n";
            $xml .= "      <g:title>" . htmlspecialchars($title, ENT_XML1, 'UTF-8') . "</g:title>\n";
            $xml .= "      <g:description>" . htmlspecialchars($desc, ENT_XML1, 'UTF-8') . "</g:description>\n";
            $xml .= "      <g:link>" . htmlspecialchars($link, ENT_XML1, 'UTF-8') . "</g:link>\n";
            $xml .= "      <g:image_link>" . htmlspecialchars($imageLink, ENT_XML1, 'UTF-8') . "</g:image_link>\n";
            $xml .= "      <g:availability>in_stock</g:availability>\n";
            $xml .= "      <g:price>{$priceStr}</g:price>\n";
            $xml .= "      <g:google_product_category>Media &gt; Books &gt; E-Books</g:google_product_category>\n";
            $xml .= "      <g:product_type>E-Books &gt; Digital</g:product_type>\n";
            $xml .= "      <g:brand>আইডিয়া প্রকাশন</g:brand>\n";
            $xml .= "      <g:condition>new</g:condition>\n";
            $xml .= "      <g:identifier_exists>no</g:identifier_exists>\n";
            $xml .= "    </item>\n";
        }

        $xml .= "  </channel>\n";
        $xml .= '</rss>';

        // Keep static file updated for fast crawler access
        try {
            @file_put_contents(public_path('google-merchant-feed.xml'), $xml);
        } catch (\Throwable $e) {}

        return response($xml, 200, [
            'Content-Type' => 'application/xml; charset=utf-8',
        ]);
    }

    /**
     * Google Merchant Center Product Feed (TSV Tab-Delimited text format).
     */
    public function feedTsv(): Response
    {
        $baseUrl = $this->getBaseUrl();
        $defaultLogo = url(asset('images/logo.png'));

        $headers = [
            'id', 'title', 'description', 'link', 'image_link', 'availability', 
            'price', 'sale_price', 'brand', 'google_product_category', 
            'product_type', 'condition', 'identifier_exists', 'isbn', 'shipping(country:price)'
        ];

        $tsv = implode("\t", $headers) . "\n";

        $books = Book::where('is_active', true)
            ->where(function($q) {
                $q->whereNull('mod_status')->orWhere('mod_status', 'approved');
            })
            ->with(['authors', 'category', 'publisher'])
            ->latest('id')
            ->get();

        foreach ($books as $b) {
            $id = 'BOOK-' . $b->id;
            $slug = $b->slug ?: $b->id;
            $link = $baseUrl . '/books/' . $slug;

            $authorName = $b->authors->isNotEmpty() ? $b->authors->pluck('name')->join(', ') : ($b->author_name ?: 'আইডিয়া প্রকাশন');
            $title = str_replace(["\t", "\r", "\n"], ' ', $b->title . ' — ' . $authorName);

            $rawDesc = strip_tags((string)($b->summary ?: ($b->description ?: $title)));
            $desc = Str::limit(str_replace(["\t", "\r", "\n"], ' ', $rawDesc), 4500);

            $imageLink = $defaultLogo;
            if (!empty($b->cover_image)) {
                $imageLink = str_starts_with((string)$b->cover_image, 'http') ? $b->cover_image : $baseUrl . '/storage/' . ltrim((string)$b->cover_image, '/');
            }

            $regPrice = max(1, (float)($b->price > 0 ? $b->price : ($b->discount_price > 0 ? $b->discount_price : 100)));
            $salePrice = ($b->discount_price > 0 && $b->discount_price < $regPrice) ? (float)$b->discount_price : null;

            $priceStr = number_format($regPrice, 2, '.', '') . ' BDT';
            $salePriceStr = $salePrice ? (number_format($salePrice, 2, '.', '') . ' BDT') : '';

            $stockStatus = strtolower((string)$b->stock_status);
            $availability = ($stockStatus === 'out_of_stock' || (is_numeric($b->stock_quantity) && $b->stock_quantity <= 0)) ? 'out_of_stock' : 'in_stock';
            if ($stockStatus === 'pre_order') {
                $availability = 'preorder';
            }

            $brand = str_replace(["\t", "\r", "\n"], ' ', $b->publisher?->name ?: ($b->author_name ?: 'আইডিয়া প্রকাশন'));
            $catName = str_replace(["\t", "\r", "\n"], ' ', $b->category?->name ?: 'বাংলা বই');
            $isbn = trim((string)$b->isbn);
            $identifierExists = !empty($isbn) ? 'yes' : 'no';

            $row = [
                $id,
                $title,
                $desc,
                $link,
                $imageLink,
                $availability,
                $priceStr,
                $salePriceStr,
                $brand,
                'Media > Books > Print Books',
                "Books > {$catName}",
                'new',
                $identifierExists,
                $isbn,
                'BD:60.00 BDT',
            ];

            $tsv .= implode("\t", $row) . "\n";
        }

        return response($tsv, 200, [
            'Content-Type' => 'text/tab-separated-values; charset=utf-8',
        ]);
    }
}
