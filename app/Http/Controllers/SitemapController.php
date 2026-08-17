<?php

namespace App\Http\Controllers;

use Illuminate\Http\Response;
use Modules\Blog\Models\BlogPost;
use Modules\Blog\Models\BlogCategory;
use App\Models\Book;
use App\Models\Author;
use App\Models\Publisher;
use App\Models\Category;

class SitemapController extends Controller
{
    /**
     * Display the dynamic XML sitemap and refresh the static public/sitemap.xml file.
     */
    public function index(): Response
    {
        $xml = $this->generateSitemapXml();

        // Keep static public/sitemap.xml refreshed for search engines and direct crawler access
        try {
            @file_put_contents(public_path('sitemap.xml'), $xml);
        } catch (\Throwable $e) {}

        return response($xml, 200, [
            'Content-Type' => 'application/xml; charset=utf-8',
        ]);
    }

    /**
     * Build the valid SEO-compliant XML sitemap.
     */
    public function generateSitemapXml(): string
    {
        $baseUrl = config('app.url', 'https://www.ideaabd.com');
        $baseUrl = rtrim($baseUrl, '/');

        $xml = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
        $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9" '
              . 'xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" '
              . 'xsi:schemaLocation="http://www.sitemaps.org/schemas/sitemap/0.9 http://www.sitemaps.org/schemas/sitemap/0.9/sitemap.xsd">' . "\n";

        // Static core routes
        $staticRoutes = [
            ['url' => '/', 'priority' => '1.0', 'freq' => 'daily'],
            ['url' => '/blog', 'priority' => '0.9', 'freq' => 'daily'],
            ['url' => '/books', 'priority' => '0.9', 'freq' => 'daily'],
            ['url' => '/ebooks', 'priority' => '0.9', 'freq' => 'weekly'],
            ['url' => '/authors', 'priority' => '0.8', 'freq' => 'weekly'],
            ['url' => '/publishers', 'priority' => '0.8', 'freq' => 'weekly'],
            ['url' => '/webzine', 'priority' => '0.8', 'freq' => 'weekly'],
            ['url' => '/research', 'priority' => '0.8', 'freq' => 'weekly'],
            ['url' => '/hub', 'priority' => '0.8', 'freq' => 'monthly'],
            ['url' => '/about', 'priority' => '0.7', 'freq' => 'monthly'],
            ['url' => '/contact', 'priority' => '0.7', 'freq' => 'monthly'],
            ['url' => '/register/author', 'priority' => '0.8', 'freq' => 'monthly'],
            ['url' => '/register/customer', 'priority' => '0.6', 'freq' => 'monthly'],
        ];

        $now = date('Y-m-d');

        foreach ($staticRoutes as $r) {
            $xml .= "  <url>\n";
            $xml .= "    <loc>" . htmlspecialchars($baseUrl . $r['url']) . "</loc>\n";
            $xml .= "    <lastmod>{$now}</lastmod>\n";
            $xml .= "    <changefreq>" . $r['freq'] . "</changefreq>\n";
            $xml .= "    <priority>" . $r['priority'] . "</priority>\n";
            $xml .= "  </url>\n";
        }

        // Blog Categories
        if (\Illuminate\Support\Facades\Schema::hasTable('blog_categories')) {
            try {
                $blogCats = BlogCategory::where('is_active', true)->get();
                foreach ($blogCats as $bCat) {
                    $lastMod = $bCat->updated_at ? $bCat->updated_at->format('Y-m-d') : $now;
                    $xml .= "  <url>\n";
                    $xml .= "    <loc>" . htmlspecialchars($baseUrl . '/blog/category/' . $bCat->slug) . "</loc>\n";
                    $xml .= "    <lastmod>{$lastMod}</lastmod>\n";
                    $xml .= "    <changefreq>daily</changefreq>\n";
                    $xml .= "    <priority>0.85</priority>\n";
                    $xml .= "  </url>\n";
                }
            } catch (\Throwable $e) {}
        }

        // Published Blog Posts
        if (\Illuminate\Support\Facades\Schema::hasTable('blog_posts')) {
            try {
                $blogPosts = BlogPost::where('status', 'published')->latest('published_at')->get();
                foreach ($blogPosts as $post) {
                    $lastMod = $post->published_at ? $post->published_at->format('Y-m-d') : ($post->updated_at ? $post->updated_at->format('Y-m-d') : $now);
                    $xml .= "  <url>\n";
                    $xml .= "    <loc>" . htmlspecialchars($baseUrl . '/blog/' . $post->slug) . "</loc>\n";
                    $xml .= "    <lastmod>{$lastMod}</lastmod>\n";
                    $xml .= "    <changefreq>weekly</changefreq>\n";
                    $xml .= "    <priority>0.8</priority>\n";
                    $xml .= "  </url>\n";
                }
            } catch (\Throwable $e) {}
        }

        // Books
        if (\Illuminate\Support\Facades\Schema::hasTable('books')) {
            try {
                $books = Book::where('is_active', true)->latest('id')->take(1000)->get();
                foreach ($books as $b) {
                    $lastMod = $b->updated_at ? $b->updated_at->format('Y-m-d') : $now;
                    $slug = $b->slug ?: $b->id;
                    $xml .= "  <url>\n";
                    $xml .= "    <loc>" . htmlspecialchars($baseUrl . '/books/' . $slug) . "</loc>\n";
                    $xml .= "    <lastmod>{$lastMod}</lastmod>\n";
                    $xml .= "    <changefreq>weekly</changefreq>\n";
                    $xml .= "    <priority>0.8</priority>\n";
                    $xml .= "  </url>\n";
                }
            } catch (\Throwable $e) {}
        }

        // Authors
        if (\Illuminate\Support\Facades\Schema::hasTable('authors')) {
            try {
                $authors = Author::where('is_active', true)->latest('id')->take(500)->get();
                foreach ($authors as $a) {
                    $lastMod = $a->updated_at ? $a->updated_at->format('Y-m-d') : $now;
                    $slug = $a->slug ?: $a->id;
                    $xml .= "  <url>\n";
                    $xml .= "    <loc>" . htmlspecialchars($baseUrl . '/authors/' . $slug) . "</loc>\n";
                    $xml .= "    <lastmod>{$lastMod}</lastmod>\n";
                    $xml .= "    <changefreq>monthly</changefreq>\n";
                    $xml .= "    <priority>0.7</priority>\n";
                    $xml .= "  </url>\n";
                }
            } catch (\Throwable $e) {}
        }

        // Publishers
        if (\Illuminate\Support\Facades\Schema::hasTable('publishers')) {
            try {
                $publishers = Publisher::where('is_active', true)->latest('id')->take(500)->get();
                foreach ($publishers as $p) {
                    $lastMod = $p->updated_at ? $p->updated_at->format('Y-m-d') : $now;
                    $slug = $p->slug ?: $p->id;
                    $xml .= "  <url>\n";
                    $xml .= "    <loc>" . htmlspecialchars($baseUrl . '/publishers/' . $slug) . "</loc>\n";
                    $xml .= "    <lastmod>{$lastMod}</lastmod>\n";
                    $xml .= "    <changefreq>monthly</changefreq>\n";
                    $xml .= "    <priority>0.7</priority>\n";
                    $xml .= "  </url>\n";
                }
            } catch (\Throwable $e) {}
        }

        $xml .= '</urlset>';
        return $xml;
    }
}
