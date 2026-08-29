<?php

namespace App\Http\Controllers;

use Illuminate\Http\Response;
use Illuminate\Support\Facades\Schema;
use Modules\Blog\Models\BlogPost;
use Modules\Blog\Models\BlogCategory;
use Modules\Book\Models\Book;
use Modules\Author\Models\Author;
use Modules\Publisher\Models\Publisher;
use Modules\Book\Models\Category;
use Modules\Ebook\Models\Ebook;
use Modules\Webzine\Models\Webzine;

class SitemapController extends Controller
{
    /**
     * Display the master XML sitemap and update the public/sitemap.xml file.
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
            'X-Robots-Tag' => 'noindex, follow',
        ]);
    }

    /**
     * Build the valid SEO-compliant XML sitemap with Google Images and alternate tags.
     */
    public function generateSitemapXml(): string
    {
        $baseUrl = config('app.url', 'https://www.ideaabd.com');
        if (str_contains($baseUrl, 'localhost') || str_contains($baseUrl, '127.0.0.1')) {
            $baseUrl = 'https://www.ideaabd.com';
        }
        $baseUrl = rtrim($baseUrl, '/');

        $xml = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
        $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9" '
              . 'xmlns:image="http://www.google.com/schemas/sitemap-image/1.1" '
              . 'xmlns:xhtml="http://www.w3.org/1999/xhtml" '
              . 'xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" '
              . 'xsi:schemaLocation="http://www.sitemaps.org/schemas/sitemap/0.9 http://www.sitemaps.org/schemas/sitemap/0.9/sitemap.xsd">' . "\n";

        $now = date('Y-m-d');

        // Static core landing pages
        $staticRoutes = [
            ['url' => '/', 'priority' => '1.0', 'freq' => 'always'],
            ['url' => '/books', 'priority' => '0.95', 'freq' => 'hourly'],
            ['url' => '/blog', 'priority' => '0.95', 'freq' => 'hourly'],
            ['url' => '/ebooks', 'priority' => '0.90', 'freq' => 'daily'],
            ['url' => '/authors', 'priority' => '0.85', 'freq' => 'daily'],
            ['url' => '/publishers', 'priority' => '0.85', 'freq' => 'daily'],
            ['url' => '/webzine', 'priority' => '0.85', 'freq' => 'daily'],
            ['url' => '/research', 'priority' => '0.80', 'freq' => 'weekly'],
            ['url' => '/hub', 'priority' => '0.80', 'freq' => 'weekly'],
            ['url' => '/about', 'priority' => '0.70', 'freq' => 'monthly'],
            ['url' => '/contact', 'priority' => '0.70', 'freq' => 'monthly'],
            ['url' => '/register/author', 'priority' => '0.75', 'freq' => 'monthly'],
            ['url' => '/register/publisher', 'priority' => '0.75', 'freq' => 'monthly'],
        ];

        foreach ($staticRoutes as $r) {
            $loc = htmlspecialchars($baseUrl . $r['url']);
            $xml .= "  <url>\n";
            $xml .= "    <loc>{$loc}</loc>\n";
            $xml .= "    <lastmod>{$now}</lastmod>\n";
            $xml .= "    <changefreq>{$r['freq']}</changefreq>\n";
            $xml .= "    <priority>{$r['priority']}</priority>\n";
            $xml .= "    <xhtml:link rel=\"alternate\" hreflang=\"bn\" href=\"{$loc}\"/>\n";
            $xml .= "  </url>\n";
        }

        // Blog Categories
        if (Schema::hasTable('blog_categories')) {
            try {
                $blogCats = BlogCategory::where('is_active', true)->get();
                foreach ($blogCats as $bCat) {
                    $lastMod = $bCat->updated_at ? $bCat->updated_at->format('Y-m-d') : $now;
                    $loc = htmlspecialchars($baseUrl . '/blog/category/' . $bCat->slug);
                    $xml .= "  <url>\n";
                    $xml .= "    <loc>{$loc}</loc>\n";
                    $xml .= "    <lastmod>{$lastMod}</lastmod>\n";
                    $xml .= "    <changefreq>daily</changefreq>\n";
                    $xml .= "    <priority>0.85</priority>\n";
                    $xml .= "  </url>\n";
                }
            } catch (\Throwable $e) {}
        }

        // Published Blog Posts (Articles, Ideapatra)
        if (Schema::hasTable('blog_posts')) {
            try {
                $blogPosts = BlogPost::where('status', 'published')
                    ->where(function($q) {
                        $q->whereNull('mod_status')->orWhere('mod_status', 'approved');
                    })
                    ->latest('published_at')
                    ->take(2000)
                    ->get();

                foreach ($blogPosts as $post) {
                    $lastMod = $post->published_at ? $post->published_at->format('Y-m-d') : ($post->updated_at ? $post->updated_at->format('Y-m-d') : $now);
                    $loc = htmlspecialchars($baseUrl . '/blog/' . $post->slug);
                    $xml .= "  <url>\n";
                    $xml .= "    <loc>{$loc}</loc>\n";
                    $xml .= "    <lastmod>{$lastMod}</lastmod>\n";
                    $xml .= "    <changefreq>weekly</changefreq>\n";
                    $xml .= "    <priority>0.85</priority>\n";

                    if (!empty($post->featured_image) || !empty($post->cover_image)) {
                        $img = $post->cover_url ?: (str_starts_with((string)$post->featured_image, 'http') ? $post->featured_image : $baseUrl . '/storage/' . ltrim((string)$post->featured_image, '/'));
                        $xml .= "    <image:image>\n";
                        $xml .= "      <image:loc>" . htmlspecialchars($img) . "</image:loc>\n";
                        $xml .= "      <image:title>" . htmlspecialchars($post->title) . "</image:title>\n";
                        $xml .= "    </image:image>\n";
                    }

                    $xml .= "  </url>\n";
                }
            } catch (\Throwable $e) {}
        }

        // Active & Approved Books
        if (Schema::hasTable('books')) {
            try {
                $books = Book::where('is_active', true)
                    ->where(function($q) {
                        $q->whereNull('mod_status')->orWhere('mod_status', 'approved');
                    })
                    ->latest('id')
                    ->take(3000)
                    ->get();

                foreach ($books as $b) {
                    $lastMod = $b->updated_at ? $b->updated_at->format('Y-m-d') : $now;
                    $slug = $b->slug ?: $b->id;
                    $loc = htmlspecialchars($baseUrl . '/books/' . $slug);
                    $xml .= "  <url>\n";
                    $xml .= "    <loc>{$loc}</loc>\n";
                    $xml .= "    <lastmod>{$lastMod}</lastmod>\n";
                    $xml .= "    <changefreq>daily</changefreq>\n";
                    $xml .= "    <priority>0.90</priority>\n";

                    if (!empty($b->cover_image)) {
                        $img = str_starts_with((string)$b->cover_image, 'http') ? $b->cover_image : $baseUrl . '/storage/' . ltrim((string)$b->cover_image, '/');
                        $xml .= "    <image:image>\n";
                        $xml .= "      <image:loc>" . htmlspecialchars($img) . "</image:loc>\n";
                        $xml .= "      <image:title>" . htmlspecialchars($b->title . ' — ' . ($b->author_name ?: 'আইডিয়া প্রকাশন')) . "</image:title>\n";
                        $xml .= "    </image:image>\n";
                    }

                    $xml .= "  </url>\n";
                }
            } catch (\Throwable $e) {}
        }

        // Authors
        if (Schema::hasTable('authors')) {
            try {
                $authors = Author::where('is_active', true)->latest('id')->take(1000)->get();
                foreach ($authors as $a) {
                    $lastMod = $a->updated_at ? $a->updated_at->format('Y-m-d') : $now;
                    $slug = $a->slug ?: $a->id;
                    $loc = htmlspecialchars($baseUrl . '/authors/' . $slug);
                    $xml .= "  <url>\n";
                    $xml .= "    <loc>{$loc}</loc>\n";
                    $xml .= "    <lastmod>{$lastMod}</lastmod>\n";
                    $xml .= "    <changefreq>weekly</changefreq>\n";
                    $xml .= "    <priority>0.80</priority>\n";

                    if (!empty($a->avatar) || !empty($a->photo)) {
                        $img = str_starts_with((string)($a->avatar ?: $a->photo), 'http') ? ($a->avatar ?: $a->photo) : $baseUrl . '/storage/' . ltrim((string)($a->avatar ?: $a->photo), '/');
                        $xml .= "    <image:image>\n";
                        $xml .= "      <image:loc>" . htmlspecialchars($img) . "</image:loc>\n";
                        $xml .= "      <image:title>" . htmlspecialchars($a->name) . "</image:title>\n";
                        $xml .= "    </image:image>\n";
                    }

                    $xml .= "  </url>\n";
                }
            } catch (\Throwable $e) {}
        }

        // Publishers
        if (Schema::hasTable('publishers')) {
            try {
                $publishers = Publisher::where('is_active', true)->latest('id')->take(1000)->get();
                foreach ($publishers as $p) {
                    $lastMod = $p->updated_at ? $p->updated_at->format('Y-m-d') : $now;
                    $slug = $p->slug ?: $p->id;
                    $loc = htmlspecialchars($baseUrl . '/publishers/' . $slug);
                    $xml .= "  <url>\n";
                    $xml .= "    <loc>{$loc}</loc>\n";
                    $xml .= "    <lastmod>{$lastMod}</lastmod>\n";
                    $xml .= "    <changefreq>weekly</changefreq>\n";
                    $xml .= "    <priority>0.80</priority>\n";
                    $xml .= "  </url>\n";
                }
            } catch (\Throwable $e) {}
        }

        // Ebooks
        if (Schema::hasTable('ebooks')) {
            try {
                $ebooks = Ebook::where('is_active', true)->latest('id')->take(1000)->get();
                foreach ($ebooks as $eb) {
                    $lastMod = $eb->updated_at ? $eb->updated_at->format('Y-m-d') : $now;
                    $slug = $eb->slug ?: $eb->id;
                    $loc = htmlspecialchars($baseUrl . '/ebooks/' . $slug);
                    $xml .= "  <url>\n";
                    $xml .= "    <loc>{$loc}</loc>\n";
                    $xml .= "    <lastmod>{$lastMod}</lastmod>\n";
                    $xml .= "    <changefreq>weekly</changefreq>\n";
                    $xml .= "    <priority>0.80</priority>\n";
                    $xml .= "  </url>\n";
                }
            } catch (\Throwable $e) {}
        }

        // Webzines
        if (Schema::hasTable('webzines')) {
            try {
                $webzines = Webzine::where('is_active', true)->latest('id')->take(1000)->get();
                foreach ($webzines as $wz) {
                    $lastMod = $wz->updated_at ? $wz->updated_at->format('Y-m-d') : $now;
                    $slug = $wz->slug ?: $wz->id;
                    $loc = htmlspecialchars($baseUrl . '/webzines/' . $slug);
                    $xml .= "  <url>\n";
                    $xml .= "    <loc>{$loc}</loc>\n";
                    $xml .= "    <lastmod>{$lastMod}</lastmod>\n";
                    $xml .= "    <changefreq>monthly</changefreq>\n";
                    $xml .= "    <priority>0.80</priority>\n";
                    $xml .= "  </url>\n";
                }
            } catch (\Throwable $e) {}
        }

        $xml .= '</urlset>';
        return $xml;
    }

    /**
     * Generate standard RSS 2.0 / Atom feed for Google News, Feedly, and RSS crawlers.
     */
    public function feed(): Response
    {
        $baseUrl = config('app.url', 'https://www.ideaabd.com');
        if (str_contains($baseUrl, 'localhost') || str_contains($baseUrl, '127.0.0.1')) {
            $baseUrl = 'https://www.ideaabd.com';
        }
        $baseUrl = rtrim($baseUrl, '/');

        $siteName = \App\Support\SiteSetting::name() ?: 'আইডিয়া প্রকাশন';
        $siteDesc = \App\Support\SiteSetting::tagline() ?: 'অনলাইন বই ও প্রকাশনা প্ল্যাটফর্ম';

        $posts = BlogPost::where('status', 'published')
            ->latest('published_at')
            ->take(30)
            ->get();

        $rss = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
        $rss .= '<rss version="2.0" xmlns:atom="http://www.w3.org/2005/Atom" xmlns:dc="http://purl.org/dc/elements/1.1/" xmlns:content="http://purl.org/rss/1.0/modules/content/">' . "\n";
        $rss .= "  <channel>\n";
        $rss .= "    <title>" . htmlspecialchars($siteName) . "</title>\n";
        $rss .= "    <link>{$baseUrl}</link>\n";
        $rss .= "    <description>" . htmlspecialchars($siteDesc) . "</description>\n";
        $rss .= "    <language>bn-BD</language>\n";
        $rss .= "    <lastBuildDate>" . date(DATE_RSS) . "</lastBuildDate>\n";
        $rss .= "    <atom:link href=\"{$baseUrl}/feed\" rel=\"self\" type=\"application/rss+xml\" />\n";

        foreach ($posts as $post) {
            $postUrl = $baseUrl . '/blog/' . $post->slug;
            $author = $post->author_name ?: 'আইডিয়া প্রকাশন';
            $pubDate = $post->published_at ? $post->published_at->toRssString() : ($post->created_at ? $post->created_at->toRssString() : date(DATE_RSS));
            $excerpt = strip_tags((string)($post->excerpt ?: $post->subtitle ?: $post->content));
            $excerpt = mb_substr($excerpt, 0, 300, 'UTF-8');

            $rss .= "    <item>\n";
            $rss .= "      <title>" . htmlspecialchars($post->title) . "</title>\n";
            $rss .= "      <link>{$postUrl}</link>\n";
            $rss .= "      <guid isPermaLink=\"true\">{$postUrl}</guid>\n";
            $rss .= "      <dc:creator>" . htmlspecialchars($author) . "</dc:creator>\n";
            $rss .= "      <pubDate>{$pubDate}</pubDate>\n";
            $rss .= "      <description><![CDATA[{$excerpt}]]></description>\n";
            $rss .= "    </item>\n";
        }

        $rss .= "  </channel>\n";
        $rss .= '</rss>';

        return response($rss, 200, [
            'Content-Type' => 'application/rss+xml; charset=utf-8',
        ]);
    }
}
