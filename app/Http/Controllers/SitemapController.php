<?php

namespace App\Http\Controllers;

use Illuminate\Http\Response;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Route;
use Modules\Blog\Models\BlogPost;
use Modules\Blog\Models\BlogCategory;
use Modules\Blog\Models\BlogTag;
use Modules\Book\Models\Book;
use Modules\Author\Models\Author;
use Modules\Publisher\Models\Publisher;
use Modules\Book\Models\Category;
use Modules\Ebook\Models\Ebook;
use Modules\Webzine\Models\Webzine;
use Modules\Research\Models\ResearchPaper;

class SitemapController extends Controller
{
    /**
     * Get clean normalized production base URL.
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
     * Helper to start XML URLSET with proper Schema namespaces.
     */
    protected function startUrlsetXml(): string
    {
        $xml = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
        $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9" '
              . 'xmlns:image="http://www.google.com/schemas/sitemap-image/1.1" '
              . 'xmlns:xhtml="http://www.w3.org/1999/xhtml" '
              . 'xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" '
              . 'xsi:schemaLocation="http://www.sitemaps.org/schemas/sitemap/0.9 http://www.sitemaps.org/schemas/sitemap/0.9/sitemap.xsd">' . "\n";
        return $xml;
    }

    /**
     * Display the master XML sitemap containing all indexing URLs and update public/sitemap.xml.
     */
    public function index(): Response
    {
        $xml = $this->generateMasterSitemapXml();

        // Keep static public/sitemap.xml refreshed for search engine bots
        try {
            @file_put_contents(public_path('sitemap.xml'), $xml);
        } catch (\Throwable $e) {}

        return response($xml, 200, [
            'Content-Type' => 'application/xml; charset=utf-8',
            'X-Robots-Tag' => 'noindex, follow',
        ]);
    }

    /**
     * Display Google Sitemap Index linking all sub-sitemaps.
     */
    public function sitemapIndex(): Response
    {
        $baseUrl = $this->getBaseUrl();
        $now = date('Y-m-d\TH:i:sP');

        $sitemaps = [
            $baseUrl . '/sitemaps/pages.xml',
            $baseUrl . '/sitemaps/posts.xml',
            $baseUrl . '/sitemaps/books.xml',
            $baseUrl . '/sitemaps/ebooks.xml',
            $baseUrl . '/sitemaps/magazines.xml',
            $baseUrl . '/sitemaps/authors.xml',
            $baseUrl . '/sitemaps/publishers.xml',
            $baseUrl . '/sitemaps/categories.xml',
            $baseUrl . '/sitemaps/research.xml',
        ];

        $xml = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
        $xml .= '<sitemapindex xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";

        foreach ($sitemaps as $sm) {
            $xml .= "  <sitemap>\n";
            $xml .= "    <loc>" . htmlspecialchars($sm, ENT_XML1, 'UTF-8') . "</loc>\n";
            $xml .= "    <lastmod>{$now}</lastmod>\n";
            $xml .= "  </sitemap>\n";
        }

        $xml .= '</sitemapindex>';

        return response($xml, 200, [
            'Content-Type' => 'application/xml; charset=utf-8',
            'X-Robots-Tag' => 'noindex, follow',
        ]);
    }

    /**
     * Sub-sitemap for Static & Landing Pages, Navigation Menus, Submenus & Utility Pages
     */
    public function pagesSitemap(): Response
    {
        $baseUrl = $this->getBaseUrl();
        $now = date('Y-m-d');

        $staticRoutes = $this->getAllStaticAndMenuPages();

        $xml = $this->startUrlsetXml();
        foreach ($staticRoutes as $r) {
            $loc = htmlspecialchars(str_starts_with($r['url'], 'http') ? $r['url'] : ($baseUrl . '/' . ltrim($r['url'], '/')), ENT_XML1, 'UTF-8');
            $xml .= "  <url>\n";
            $xml .= "    <loc>{$loc}</loc>\n";
            $xml .= "    <lastmod>{$now}</lastmod>\n";
            $xml .= "    <changefreq>{$r['freq']}</changefreq>\n";
            $xml .= "    <priority>{$r['priority']}</priority>\n";
            $xml .= "    <xhtml:link rel=\"alternate\" hreflang=\"bn\" href=\"{$loc}\"/>\n";
            $xml .= "  </url>\n";
        }
        $xml .= '</urlset>';

        return response($xml, 200, ['Content-Type' => 'application/xml; charset=utf-8']);
    }

    /**
     * Sub-sitemap for Ideapatra Articles, Literary Works & Blog Posts
     */
    public function postsSitemap(): Response
    {
        $baseUrl = $this->getBaseUrl();
        $now = date('Y-m-d');
        $xml = $this->startUrlsetXml();

        // Ideapatra and Blog Main Portal Hubs
        $hubs = [
            ['url' => '/ideapatra', 'prio' => '0.98', 'freq' => 'hourly'],
            ['url' => '/blog', 'prio' => '0.96', 'freq' => 'hourly'],
            ['url' => '/ideapatra/write', 'prio' => '0.85', 'freq' => 'weekly'],
        ];
        foreach ($hubs as $h) {
            $hLoc = htmlspecialchars($baseUrl . $h['url'], ENT_XML1, 'UTF-8');
            $xml .= "  <url>\n";
            $xml .= "    <loc>{$hLoc}</loc>\n";
            $xml .= "    <lastmod>{$now}</lastmod>\n";
            $xml .= "    <changefreq>{$h['freq']}</changefreq>\n";
            $xml .= "    <priority>{$h['prio']}</priority>\n";
            $xml .= "    <xhtml:link rel=\"alternate\" hreflang=\"bn\" href=\"{$hLoc}\"/>\n";
            $xml .= "  </url>\n";
        }

        // All Ideapatra / Blog Categories & Submenus
        if (Schema::hasTable('blog_categories')) {
            try {
                $blogCats = BlogCategory::where('is_active', true)->get();
                foreach ($blogCats as $bCat) {
                    $lastMod = $bCat->updated_at ? $bCat->updated_at->format('Y-m-d') : $now;
                    
                    $loc1 = htmlspecialchars($baseUrl . '/blog/category/' . $bCat->slug, ENT_XML1, 'UTF-8');
                    $xml .= "  <url>\n";
                    $xml .= "    <loc>{$loc1}</loc>\n";
                    $xml .= "    <lastmod>{$lastMod}</lastmod>\n";
                    $xml .= "    <changefreq>daily</changefreq>\n";
                    $xml .= "    <priority>0.86</priority>\n";
                    $xml .= "  </url>\n";

                    $loc2 = htmlspecialchars($baseUrl . '/ideapatra/category/' . $bCat->slug, ENT_XML1, 'UTF-8');
                    $xml .= "  <url>\n";
                    $xml .= "    <loc>{$loc2}</loc>\n";
                    $xml .= "    <lastmod>{$lastMod}</lastmod>\n";
                    $xml .= "    <changefreq>daily</changefreq>\n";
                    $xml .= "    <priority>0.86</priority>\n";
                    $xml .= "  </url>\n";
                }
            } catch (\Throwable $e) {}
        }

        // All Ideapatra / Blog Tags & Submenus
        if (Schema::hasTable('blog_tags')) {
            try {
                $tags = BlogTag::all();
                foreach ($tags as $tag) {
                    $tLoc = htmlspecialchars($baseUrl . '/blog/tag/' . $tag->slug, ENT_XML1, 'UTF-8');
                    $xml .= "  <url>\n";
                    $xml .= "    <loc>{$tLoc}</loc>\n";
                    $xml .= "    <lastmod>{$now}</lastmod>\n";
                    $xml .= "    <changefreq>weekly</changefreq>\n";
                    $xml .= "    <priority>0.75</priority>\n";
                    $xml .= "  </url>\n";

                    $tLoc2 = htmlspecialchars($baseUrl . '/ideapatra/tag/' . $tag->slug, ENT_XML1, 'UTF-8');
                    $xml .= "  <url>\n";
                    $xml .= "    <loc>{$tLoc2}</loc>\n";
                    $xml .= "    <lastmod>{$now}</lastmod>\n";
                    $xml .= "    <changefreq>weekly</changefreq>\n";
                    $xml .= "    <priority>0.75</priority>\n";
                    $xml .= "  </url>\n";
                }
            } catch (\Throwable $e) {}
        }

        // All Published Blog Posts & Ideapatra Articles
        if (Schema::hasTable('blog_posts')) {
            try {
                $blogPosts = BlogPost::where('status', 'published')
                    ->where(function($q) {
                        $q->whereNull('mod_status')->orWhere('mod_status', 'approved');
                    })
                    ->latest('published_at')
                    ->take(5000)
                    ->get();

                foreach ($blogPosts as $post) {
                    $lastMod = $post->published_at ? $post->published_at->format('Y-m-d') : ($post->updated_at ? $post->updated_at->format('Y-m-d') : $now);
                    $loc = htmlspecialchars($baseUrl . '/blog/' . $post->slug, ENT_XML1, 'UTF-8');
                    $xml .= "  <url>\n";
                    $xml .= "    <loc>{$loc}</loc>\n";
                    $xml .= "    <lastmod>{$lastMod}</lastmod>\n";
                    $xml .= "    <changefreq>weekly</changefreq>\n";
                    $xml .= "    <priority>0.88</priority>\n";
                    $xml .= "    <xhtml:link rel=\"alternate\" hreflang=\"bn\" href=\"{$loc}\"/>\n";

                    $ideapatraLoc = htmlspecialchars($baseUrl . '/ideapatra/' . $post->slug, ENT_XML1, 'UTF-8');
                    $xml .= "    <xhtml:link rel=\"alternate\" hreflang=\"bn-BD\" href=\"{$ideapatraLoc}\"/>\n";

                    if (!empty($post->featured_image) || !empty($post->cover_image)) {
                        $img = $post->cover_url ?: (str_starts_with((string)$post->featured_image, 'http') ? $post->featured_image : $baseUrl . '/storage/' . ltrim((string)$post->featured_image, '/'));
                        $xml .= "    <image:image>\n";
                        $xml .= "      <image:loc>" . htmlspecialchars($img, ENT_XML1, 'UTF-8') . "</image:loc>\n";
                        $xml .= "      <image:title>" . htmlspecialchars($post->title, ENT_XML1, 'UTF-8') . "</image:title>\n";
                        $xml .= "    </image:image>\n";
                    }

                    $xml .= "  </url>\n";
                }
            } catch (\Throwable $e) {}
        }

        $xml .= '</urlset>';
        return response($xml, 200, ['Content-Type' => 'application/xml; charset=utf-8']);
    }

    /**
     * Sub-sitemap for Books, Bookshop Menus, Submenus, Category Filters & Book Details
     */
    public function booksSitemap(): Response
    {
        $baseUrl = $this->getBaseUrl();
        $now = date('Y-m-d');
        $xml = $this->startUrlsetXml();

        // 1. Bookshop Core Menus, Submenus & Filters
        $bookshopMenus = [
            ['url' => '/books', 'prio' => '0.98', 'freq' => 'hourly'],
            ['url' => '/books?sort=bestselling', 'prio' => '0.92', 'freq' => 'daily'],
            ['url' => '/books?sort=newest', 'prio' => '0.92', 'freq' => 'daily'],
            ['url' => '/books?sort=popular', 'prio' => '0.90', 'freq' => 'daily'],
            ['url' => '/books?filter=boimela-2026', 'prio' => '0.95', 'freq' => 'daily'],
            ['url' => '/books?filter=mega-discount', 'prio' => '0.93', 'freq' => 'daily'],
            ['url' => '/books?category=pshcimbnger-bi', 'prio' => '0.90', 'freq' => 'daily'],
        ];

        foreach ($bookshopMenus as $bm) {
            $bmLoc = htmlspecialchars($baseUrl . $bm['url'], ENT_XML1, 'UTF-8');
            $xml .= "  <url>\n";
            $xml .= "    <loc>{$bmLoc}</loc>\n";
            $xml .= "    <lastmod>{$now}</lastmod>\n";
            $xml .= "    <changefreq>{$bm['freq']}</changefreq>\n";
            $xml .= "    <priority>{$bm['prio']}</priority>\n";
            $xml .= "  </url>\n";
        }

        // 2. All Book Categories & Subcategories (Submenus)
        if (Schema::hasTable('categories')) {
            try {
                $bookCats = Category::where('is_active', true)->get();
                foreach ($bookCats as $bCat) {
                    $lastMod = $bCat->updated_at ? $bCat->updated_at->format('Y-m-d') : $now;
                    
                    // Bookshop query filter route
                    $cLoc1 = htmlspecialchars($baseUrl . '/books?category=' . $bCat->slug, ENT_XML1, 'UTF-8');
                    $xml .= "  <url>\n";
                    $xml .= "    <loc>{$cLoc1}</loc>\n";
                    $xml .= "    <lastmod>{$lastMod}</lastmod>\n";
                    $xml .= "    <changefreq>daily</changefreq>\n";
                    $xml .= "    <priority>0.88</priority>\n";
                    $xml .= "  </url>\n";
                }
            } catch (\Throwable $e) {}
        }

        // 3. All Active Individual Books
        if (Schema::hasTable('books')) {
            try {
                $books = Book::where('is_active', true)
                    ->where(function($q) {
                        $q->whereNull('mod_status')->orWhere('mod_status', 'approved');
                    })
                    ->latest('id')
                    ->take(5000)
                    ->get();

                foreach ($books as $b) {
                    $lastMod = $b->updated_at ? $b->updated_at->format('Y-m-d') : $now;
                    $slug = $b->slug ?: $b->id;
                    $loc = htmlspecialchars($baseUrl . '/books/' . $slug, ENT_XML1, 'UTF-8');
                    $xml .= "  <url>\n";
                    $xml .= "    <loc>{$loc}</loc>\n";
                    $xml .= "    <lastmod>{$lastMod}</lastmod>\n";
                    $xml .= "    <changefreq>daily</changefreq>\n";
                    $xml .= "    <priority>0.92</priority>\n";

                    if (!empty($b->cover_image)) {
                        $img = str_starts_with((string)$b->cover_image, 'http') ? $b->cover_image : $baseUrl . '/storage/' . ltrim((string)$b->cover_image, '/');
                        $xml .= "    <image:image>\n";
                        $xml .= "      <image:loc>" . htmlspecialchars($img, ENT_XML1, 'UTF-8') . "</image:loc>\n";
                        $xml .= "      <image:title>" . htmlspecialchars($b->title . ' — ' . ($b->author_name ?: 'আইডিয়া প্রকাশন'), ENT_XML1, 'UTF-8') . "</image:title>\n";
                        $xml .= "    </image:image>\n";
                    }

                    $xml .= "  </url>\n";
                }
            } catch (\Throwable $e) {}
        }

        $xml .= '</urlset>';
        return response($xml, 200, ['Content-Type' => 'application/xml; charset=utf-8']);
    }

    /**
     * Sub-sitemap for E-books, Ebook Menus & Submenus
     */
    public function ebooksSitemap(): Response
    {
        $baseUrl = $this->getBaseUrl();
        $now = date('Y-m-d');
        $xml = $this->startUrlsetXml();

        // E-books Hub & Filter Menus
        $ebookMenus = [
            ['url' => '/ebooks', 'prio' => '0.92', 'freq' => 'daily'],
            ['url' => '/ebooks?filter=free', 'prio' => '0.88', 'freq' => 'weekly'],
            ['url' => '/ebooks?filter=premium', 'prio' => '0.88', 'freq' => 'weekly'],
        ];

        foreach ($ebookMenus as $em) {
            $emLoc = htmlspecialchars($baseUrl . $em['url'], ENT_XML1, 'UTF-8');
            $xml .= "  <url>\n";
            $xml .= "    <loc>{$emLoc}</loc>\n";
            $xml .= "    <lastmod>{$now}</lastmod>\n";
            $xml .= "    <changefreq>{$em['freq']}</changefreq>\n";
            $xml .= "    <priority>{$em['prio']}</priority>\n";
            $xml .= "  </url>\n";
        }

        // All E-books
        if (Schema::hasTable('ebooks')) {
            try {
                $ebooks = Ebook::where('is_active', true)->latest('id')->take(2000)->get();
                foreach ($ebooks as $eb) {
                    $lastMod = $eb->updated_at ? $eb->updated_at->format('Y-m-d') : $now;
                    $slug = $eb->slug ?: $eb->id;
                    $loc = htmlspecialchars($baseUrl . '/ebooks/' . $slug, ENT_XML1, 'UTF-8');
                    $xml .= "  <url>\n";
                    $xml .= "    <loc>{$loc}</loc>\n";
                    $xml .= "    <lastmod>{$lastMod}</lastmod>\n";
                    $xml .= "    <changefreq>weekly</changefreq>\n";
                    $xml .= "    <priority>0.85</priority>\n";

                    if (!empty($eb->cover_image)) {
                        $img = str_starts_with((string)$eb->cover_image, 'http') ? $eb->cover_image : $baseUrl . '/storage/' . ltrim((string)$eb->cover_image, '/');
                        $xml .= "    <image:image>\n";
                        $xml .= "      <image:loc>" . htmlspecialchars($img, ENT_XML1, 'UTF-8') . "</image:loc>\n";
                        $xml .= "      <image:title>" . htmlspecialchars($eb->title . ' (ই-বুক)', ENT_XML1, 'UTF-8') . "</image:title>\n";
                        $xml .= "    </image:image>\n";
                    }

                    $xml .= "  </url>\n";
                }
            } catch (\Throwable $e) {}
        }

        $xml .= '</urlset>';
        return response($xml, 200, ['Content-Type' => 'application/xml; charset=utf-8']);
    }

    /**
     * Sub-sitemap for Magazines & Webzines
     */
    public function magazinesSitemap(): Response
    {
        $baseUrl = $this->getBaseUrl();
        $now = date('Y-m-d');
        $xml = $this->startUrlsetXml();

        // Main Webzine Landing Page
        $mainLoc = htmlspecialchars($baseUrl . '/webzines', ENT_XML1, 'UTF-8');
        $xml .= "  <url>\n";
        $xml .= "    <loc>{$mainLoc}</loc>\n";
        $xml .= "    <lastmod>{$now}</lastmod>\n";
        $xml .= "    <changefreq>daily</changefreq>\n";
        $xml .= "    <priority>0.88</priority>\n";
        $xml .= "  </url>\n";

        if (Schema::hasTable('webzines')) {
            try {
                $webzines = Webzine::where('is_active', true)->latest('id')->take(1000)->get();
                foreach ($webzines as $wz) {
                    $lastMod = $wz->updated_at ? $wz->updated_at->format('Y-m-d') : $now;
                    $slug = $wz->slug ?: $wz->id;
                    $loc = htmlspecialchars($baseUrl . '/webzines/' . $slug, ENT_XML1, 'UTF-8');
                    $xml .= "  <url>\n";
                    $xml .= "    <loc>{$loc}</loc>\n";
                    $xml .= "    <lastmod>{$lastMod}</lastmod>\n";
                    $xml .= "    <changefreq>monthly</changefreq>\n";
                    $xml .= "    <priority>0.82</priority>\n";

                    if (!empty($wz->cover_image)) {
                        $img = str_starts_with((string)$wz->cover_image, 'http') ? $wz->cover_image : $baseUrl . '/storage/' . ltrim((string)$wz->cover_image, '/');
                        $xml .= "    <image:image>\n";
                        $xml .= "      <image:loc>" . htmlspecialchars($img, ENT_XML1, 'UTF-8') . "</image:loc>\n";
                        $xml .= "      <image:title>" . htmlspecialchars($wz->title, ENT_XML1, 'UTF-8') . "</image:title>\n";
                        $xml .= "    </image:image>\n";
                    }

                    $xml .= "  </url>\n";
                }
            } catch (\Throwable $e) {}
        }

        $xml .= '</urlset>';
        return response($xml, 200, ['Content-Type' => 'application/xml; charset=utf-8']);
    }

    /**
     * Sub-sitemap for Authors
     */
    public function authorsSitemap(): Response
    {
        $baseUrl = $this->getBaseUrl();
        $now = date('Y-m-d');
        $xml = $this->startUrlsetXml();

        // Main Authors Page
        $mainAuthors = htmlspecialchars($baseUrl . '/authors', ENT_XML1, 'UTF-8');
        $xml .= "  <url>\n";
        $xml .= "    <loc>{$mainAuthors}</loc>\n";
        $xml .= "    <lastmod>{$now}</lastmod>\n";
        $xml .= "    <changefreq>daily</changefreq>\n";
        $xml .= "    <priority>0.90</priority>\n";
        $xml .= "  </url>\n";

        if (Schema::hasTable('authors')) {
            try {
                $authors = Author::where('is_active', true)->latest('id')->take(2000)->get();
                foreach ($authors as $a) {
                    $lastMod = $a->updated_at ? $a->updated_at->format('Y-m-d') : $now;
                    $slug = $a->slug ?: $a->id;
                    $loc = htmlspecialchars($baseUrl . '/authors/' . $slug, ENT_XML1, 'UTF-8');
                    $xml .= "  <url>\n";
                    $xml .= "    <loc>{$loc}</loc>\n";
                    $xml .= "    <lastmod>{$lastMod}</lastmod>\n";
                    $xml .= "    <changefreq>weekly</changefreq>\n";
                    $xml .= "    <priority>0.82</priority>\n";

                    if (!empty($a->avatar) || !empty($a->photo)) {
                        $img = str_starts_with((string)($a->avatar ?: $a->photo), 'http') ? ($a->avatar ?: $a->photo) : $baseUrl . '/storage/' . ltrim((string)($a->avatar ?: $a->photo), '/');
                        $xml .= "    <image:image>\n";
                        $xml .= "      <image:loc>" . htmlspecialchars($img, ENT_XML1, 'UTF-8') . "</image:loc>\n";
                        $xml .= "      <image:title>" . htmlspecialchars($a->name . ' — লেখক পরিচিতি', ENT_XML1, 'UTF-8') . "</image:title>\n";
                        $xml .= "    </image:image>\n";
                    }

                    $xml .= "  </url>\n";
                }
            } catch (\Throwable $e) {}
        }

        $xml .= '</urlset>';
        return response($xml, 200, ['Content-Type' => 'application/xml; charset=utf-8']);
    }

    /**
     * Sub-sitemap for Publishers
     */
    public function publishersSitemap(): Response
    {
        $baseUrl = $this->getBaseUrl();
        $now = date('Y-m-d');
        $xml = $this->startUrlsetXml();

        // Main Publishers Page
        $mainPublishers = htmlspecialchars($baseUrl . '/publishers', ENT_XML1, 'UTF-8');
        $xml .= "  <url>\n";
        $xml .= "    <loc>{$mainPublishers}</loc>\n";
        $xml .= "    <lastmod>{$now}</lastmod>\n";
        $xml .= "    <changefreq>daily</changefreq>\n";
        $xml .= "    <priority>0.85</priority>\n";
        $xml .= "  </url>\n";

        if (Schema::hasTable('publishers')) {
            try {
                $publishers = Publisher::where('is_active', true)->latest('id')->take(1000)->get();
                foreach ($publishers as $p) {
                    $lastMod = $p->updated_at ? $p->updated_at->format('Y-m-d') : $now;
                    $slug = $p->slug ?: $p->id;
                    $loc = htmlspecialchars($baseUrl . '/publishers/' . $slug, ENT_XML1, 'UTF-8');
                    $xml .= "  <url>\n";
                    $xml .= "    <loc>{$loc}</loc>\n";
                    $xml .= "    <lastmod>{$lastMod}</lastmod>\n";
                    $xml .= "    <changefreq>weekly</changefreq>\n";
                    $xml .= "    <priority>0.80</priority>\n";

                    if (!empty($p->logo)) {
                        $img = str_starts_with((string)$p->logo, 'http') ? $p->logo : $baseUrl . '/storage/' . ltrim((string)$p->logo, '/');
                        $xml .= "    <image:image>\n";
                        $xml .= "      <image:loc>" . htmlspecialchars($img, ENT_XML1, 'UTF-8') . "</image:loc>\n";
                        $xml .= "      <image:title>" . htmlspecialchars($p->name . ' — প্রকাশনী', ENT_XML1, 'UTF-8') . "</image:title>\n";
                        $xml .= "    </image:image>\n";
                    }

                    $xml .= "  </url>\n";
                }
            } catch (\Throwable $e) {}
        }

        $xml .= '</urlset>';
        return response($xml, 200, ['Content-Type' => 'application/xml; charset=utf-8']);
    }

    /**
     * Sub-sitemap for All Categories, Subcategories & Tags
     */
    public function categoriesSitemap(): Response
    {
        $baseUrl = $this->getBaseUrl();
        $now = date('Y-m-d');
        $xml = $this->startUrlsetXml();

        // 1. Blog / Ideapatra Categories & Submenus
        if (Schema::hasTable('blog_categories')) {
            try {
                $blogCats = BlogCategory::where('is_active', true)->get();
                foreach ($blogCats as $bCat) {
                    $lastMod = $bCat->updated_at ? $bCat->updated_at->format('Y-m-d') : $now;
                    $loc = htmlspecialchars($baseUrl . '/blog/category/' . $bCat->slug, ENT_XML1, 'UTF-8');
                    $xml .= "  <url>\n";
                    $xml .= "    <loc>{$loc}</loc>\n";
                    $xml .= "    <lastmod>{$lastMod}</lastmod>\n";
                    $xml .= "    <changefreq>daily</changefreq>\n";
                    $xml .= "    <priority>0.85</priority>\n";
                    $xml .= "  </url>\n";

                    $ideaLoc = htmlspecialchars($baseUrl . '/ideapatra/category/' . $bCat->slug, ENT_XML1, 'UTF-8');
                    $xml .= "  <url>\n";
                    $xml .= "    <loc>{$ideaLoc}</loc>\n";
                    $xml .= "    <lastmod>{$lastMod}</lastmod>\n";
                    $xml .= "    <changefreq>daily</changefreq>\n";
                    $xml .= "    <priority>0.85</priority>\n";
                    $xml .= "  </url>\n";
                }
            } catch (\Throwable $e) {}
        }

        // 2. Blog / Ideapatra Tags
        if (Schema::hasTable('blog_tags')) {
            try {
                $tags = BlogTag::all();
                foreach ($tags as $tag) {
                    $loc = htmlspecialchars($baseUrl . '/blog/tag/' . $tag->slug, ENT_XML1, 'UTF-8');
                    $xml .= "  <url>\n";
                    $xml .= "    <loc>{$loc}</loc>\n";
                    $xml .= "    <lastmod>{$now}</lastmod>\n";
                    $xml .= "    <changefreq>weekly</changefreq>\n";
                    $xml .= "    <priority>0.75</priority>\n";
                    $xml .= "  </url>\n";
                }
            } catch (\Throwable $e) {}
        }

        // 3. Bookshop Categories & Subcategories
        if (Schema::hasTable('categories')) {
            try {
                $bookCats = Category::where('is_active', true)->get();
                foreach ($bookCats as $bCat) {
                    $lastMod = $bCat->updated_at ? $bCat->updated_at->format('Y-m-d') : $now;
                    $loc = htmlspecialchars($baseUrl . '/books?category=' . $bCat->slug, ENT_XML1, 'UTF-8');
                    $xml .= "  <url>\n";
                    $xml .= "    <loc>{$loc}</loc>\n";
                    $xml .= "    <lastmod>{$lastMod}</lastmod>\n";
                    $xml .= "    <changefreq>daily</changefreq>\n";
                    $xml .= "    <priority>0.85</priority>\n";
                    $xml .= "  </url>\n";
                }
            } catch (\Throwable $e) {}
        }

        $xml .= '</urlset>';
        return response($xml, 200, ['Content-Type' => 'application/xml; charset=utf-8']);
    }

    /**
     * Sub-sitemap for Research Papers
     */
    public function researchSitemap(): Response
    {
        $baseUrl = $this->getBaseUrl();
        $now = date('Y-m-d');
        $xml = $this->startUrlsetXml();

        // Always include main research landing page
        $mainLoc = htmlspecialchars($baseUrl . '/research', ENT_XML1, 'UTF-8');
        $xml .= "  <url>\n";
        $xml .= "    <loc>{$mainLoc}</loc>\n";
        $xml .= "    <lastmod>{$now}</lastmod>\n";
        $xml .= "    <changefreq>weekly</changefreq>\n";
        $xml .= "    <priority>0.88</priority>\n";
        $xml .= "  </url>\n";

        if (Schema::hasTable('research_papers')) {
            try {
                $papers = ResearchPaper::published()->latest('id')->take(1000)->get();
                foreach ($papers as $rp) {
                    $lastMod = $rp->updated_at ? $rp->updated_at->format('Y-m-d') : $now;
                    $slug = $rp->slug ?: $rp->id;
                    $loc = htmlspecialchars($baseUrl . '/research/' . $slug, ENT_XML1, 'UTF-8');
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
        return response($xml, 200, ['Content-Type' => 'application/xml; charset=utf-8']);
    }

    /**
     * Dynamic fallback sitemap for single custom submitted page (e.g. about.xml, contact.xml, hub.xml).
     */
    public function dynamicPageSitemap(string $slug): Response
    {
        $baseUrl = $this->getBaseUrl();
        $cleanSlug = trim(str_replace('.xml', '', strtolower($slug)), '/');

        // Aliases
        if (in_array($cleanSlug, ['blog', 'articles', 'ideapatra', 'post', 'posts'])) {
            return $this->postsSitemap();
        }
        if (in_array($cleanSlug, ['book', 'books', 'shop'])) {
            return $this->booksSitemap();
        }
        if (in_array($cleanSlug, ['ebook', 'ebooks'])) {
            return $this->ebooksSitemap();
        }
        if (in_array($cleanSlug, ['magazine', 'magazines', 'webzine', 'webzines'])) {
            return $this->magazinesSitemap();
        }
        if (in_array($cleanSlug, ['author', 'authors', 'writers'])) {
            return $this->authorsSitemap();
        }
        if (in_array($cleanSlug, ['publisher', 'publishers'])) {
            return $this->publishersSitemap();
        }
        if (in_array($cleanSlug, ['category', 'categories', 'tags'])) {
            return $this->categoriesSitemap();
        }
        if (in_array($cleanSlug, ['research', 'papers'])) {
            return $this->researchSitemap();
        }

        $now = date('Y-m-d');
        $loc = htmlspecialchars($baseUrl . '/' . $cleanSlug, ENT_XML1, 'UTF-8');

        $xml = $this->startUrlsetXml();
        $xml .= "  <url>\n";
        $xml .= "    <loc>{$loc}</loc>\n";
        $xml .= "    <lastmod>{$now}</lastmod>\n";
        $xml .= "    <changefreq>weekly</changefreq>\n";
        $xml .= "    <priority>0.80</priority>\n";
        $xml .= "    <xhtml:link rel=\"alternate\" hreflang=\"bn\" href=\"{$loc}\"/>\n";
        $xml .= "  </url>\n";
        $xml .= '</urlset>';

        return response($xml, 200, ['Content-Type' => 'application/xml; charset=utf-8']);
    }

    /**
     * Master list of all static routes, main menus, submenus, filters, and custom nav items.
     */
    protected function getAllStaticAndMenuPages(): array
    {
        $pages = [
            ['url' => '/', 'priority' => '1.0', 'freq' => 'always'],
            ['url' => '/ideapatra', 'priority' => '0.98', 'freq' => 'hourly'],
            ['url' => '/books', 'priority' => '0.98', 'freq' => 'hourly'],
            ['url' => '/blog', 'priority' => '0.96', 'freq' => 'hourly'],
            ['url' => '/ebooks', 'priority' => '0.92', 'freq' => 'daily'],
            ['url' => '/authors', 'priority' => '0.92', 'freq' => 'daily'],
            ['url' => '/publishers', 'priority' => '0.88', 'freq' => 'daily'],
            ['url' => '/webzines', 'priority' => '0.88', 'freq' => 'daily'],
            ['url' => '/research', 'priority' => '0.88', 'freq' => 'weekly'],
            ['url' => '/hub', 'priority' => '0.82', 'freq' => 'weekly'],
            ['url' => '/about', 'priority' => '0.75', 'freq' => 'monthly'],
            ['url' => '/contact', 'priority' => '0.75', 'freq' => 'monthly'],
            ['url' => '/register/author', 'priority' => '0.78', 'freq' => 'monthly'],
            ['url' => '/register/publisher', 'priority' => '0.78', 'freq' => 'monthly'],
            ['url' => '/my-account', 'priority' => '0.60', 'freq' => 'monthly'],

            // Bookshop Submenus & Filter Pages
            ['url' => '/books?sort=bestselling', 'priority' => '0.92', 'freq' => 'daily'],
            ['url' => '/books?sort=newest', 'priority' => '0.92', 'freq' => 'daily'],
            ['url' => '/books?sort=popular', 'priority' => '0.90', 'freq' => 'daily'],
            ['url' => '/books?filter=boimela-2026', 'priority' => '0.95', 'freq' => 'daily'],
            ['url' => '/books?filter=mega-discount', 'priority' => '0.93', 'freq' => 'daily'],
            ['url' => '/books?category=pshcimbnger-bi', 'priority' => '0.90', 'freq' => 'daily'],

            // Ebook Submenus
            ['url' => '/ebooks?filter=free', 'priority' => '0.88', 'freq' => 'weekly'],
            ['url' => '/ebooks?filter=premium', 'priority' => '0.88', 'freq' => 'weekly'],

            // Ideapatra Submenus
            ['url' => '/ideapatra/write', 'priority' => '0.85', 'freq' => 'weekly'],
        ];

        // Include any custom nav items defined in SiteSetting::headerNav()
        try {
            $rawNav = \App\Support\SiteSetting::headerNav();
            foreach ($rawNav as $item) {
                if (!($item['is_active'] ?? true)) continue;
                $target = '';
                $rName = $item['route'] ?? '';
                if (!empty($rName) && Route::has($rName)) {
                    $target = route($rName, $item['params'] ?? []);
                } elseif (!empty($item['url'])) {
                    $target = $item['url'];
                }
                if ($target) {
                    $pages[] = [
                        'url' => $target,
                        'priority' => '0.80',
                        'freq' => 'weekly',
                    ];
                }
            }
        } catch (\Throwable $e) {}

        return $pages;
    }

    /**
     * Build the comprehensive master XML sitemap containing all pages, posts, books, authors, publishers.
     */
    public function generateMasterSitemapXml(): string
    {
        $baseUrl = $this->getBaseUrl();
        $now = date('Y-m-d');

        $xml = $this->startUrlsetXml();

        // 1. Static core landing pages & Submenus
        $staticRoutes = $this->getAllStaticAndMenuPages();
        foreach ($staticRoutes as $r) {
            $loc = htmlspecialchars(str_starts_with($r['url'], 'http') ? $r['url'] : ($baseUrl . '/' . ltrim($r['url'], '/')), ENT_XML1, 'UTF-8');
            $xml .= "  <url>\n";
            $xml .= "    <loc>{$loc}</loc>\n";
            $xml .= "    <lastmod>{$now}</lastmod>\n";
            $xml .= "    <changefreq>{$r['freq']}</changefreq>\n";
            $xml .= "    <priority>{$r['priority']}</priority>\n";
            $xml .= "    <xhtml:link rel=\"alternate\" hreflang=\"bn\" href=\"{$loc}\"/>\n";
            $xml .= "  </url>\n";
        }

        // 2. Blog & Ideapatra Categories & Submenus
        if (Schema::hasTable('blog_categories')) {
            try {
                $blogCats = BlogCategory::where('is_active', true)->get();
                foreach ($blogCats as $bCat) {
                    $lastMod = $bCat->updated_at ? $bCat->updated_at->format('Y-m-d') : $now;
                    $loc = htmlspecialchars($baseUrl . '/blog/category/' . $bCat->slug, ENT_XML1, 'UTF-8');
                    $xml .= "  <url>\n";
                    $xml .= "    <loc>{$loc}</loc>\n";
                    $xml .= "    <lastmod>{$lastMod}</lastmod>\n";
                    $xml .= "    <changefreq>daily</changefreq>\n";
                    $xml .= "    <priority>0.85</priority>\n";
                    $xml .= "  </url>\n";

                    $ideaCatLoc = htmlspecialchars($baseUrl . '/ideapatra/category/' . $bCat->slug, ENT_XML1, 'UTF-8');
                    $xml .= "  <url>\n";
                    $xml .= "    <loc>{$ideaCatLoc}</loc>\n";
                    $xml .= "    <lastmod>{$lastMod}</lastmod>\n";
                    $xml .= "    <changefreq>daily</changefreq>\n";
                    $xml .= "    <priority>0.85</priority>\n";
                    $xml .= "  </url>\n";
                }
            } catch (\Throwable $e) {}
        }

        // 3. Blog & Ideapatra Tags
        if (Schema::hasTable('blog_tags')) {
            try {
                $tags = BlogTag::all();
                foreach ($tags as $tag) {
                    $loc = htmlspecialchars($baseUrl . '/blog/tag/' . $tag->slug, ENT_XML1, 'UTF-8');
                    $xml .= "  <url>\n";
                    $xml .= "    <loc>{$loc}</loc>\n";
                    $xml .= "    <lastmod>{$now}</lastmod>\n";
                    $xml .= "    <changefreq>weekly</changefreq>\n";
                    $xml .= "    <priority>0.75</priority>\n";
                    $xml .= "  </url>\n";
                }
            } catch (\Throwable $e) {}
        }

        // 4. Published Blog Posts (Articles & Ideapatra)
        if (Schema::hasTable('blog_posts')) {
            try {
                $blogPosts = BlogPost::where('status', 'published')
                    ->where(function($q) {
                        $q->whereNull('mod_status')->orWhere('mod_status', 'approved');
                    })
                    ->latest('published_at')
                    ->take(5000)
                    ->get();

                foreach ($blogPosts as $post) {
                    $lastMod = $post->published_at ? $post->published_at->format('Y-m-d') : ($post->updated_at ? $post->updated_at->format('Y-m-d') : $now);
                    $loc = htmlspecialchars($baseUrl . '/blog/' . $post->slug, ENT_XML1, 'UTF-8');
                    $xml .= "  <url>\n";
                    $xml .= "    <loc>{$loc}</loc>\n";
                    $xml .= "    <lastmod>{$lastMod}</lastmod>\n";
                    $xml .= "    <changefreq>weekly</changefreq>\n";
                    $xml .= "    <priority>0.88</priority>\n";
                    $xml .= "    <xhtml:link rel=\"alternate\" hreflang=\"bn\" href=\"{$loc}\"/>\n";

                    if (!empty($post->featured_image) || !empty($post->cover_image)) {
                        $img = $post->cover_url ?: (str_starts_with((string)$post->featured_image, 'http') ? $post->featured_image : $baseUrl . '/storage/' . ltrim((string)$post->featured_image, '/'));
                        $xml .= "    <image:image>\n";
                        $xml .= "      <image:loc>" . htmlspecialchars($img, ENT_XML1, 'UTF-8') . "</image:loc>\n";
                        $xml .= "      <image:title>" . htmlspecialchars($post->title, ENT_XML1, 'UTF-8') . "</image:title>\n";
                        $xml .= "    </image:image>\n";
                    }

                    $xml .= "  </url>\n";
                }
            } catch (\Throwable $e) {}
        }

        // 5. Book Categories (Submenus)
        if (Schema::hasTable('categories')) {
            try {
                $bookCats = Category::where('is_active', true)->get();
                foreach ($bookCats as $bCat) {
                    $lastMod = $bCat->updated_at ? $bCat->updated_at->format('Y-m-d') : $now;
                    $loc = htmlspecialchars($baseUrl . '/books?category=' . $bCat->slug, ENT_XML1, 'UTF-8');
                    $xml .= "  <url>\n";
                    $xml .= "    <loc>{$loc}</loc>\n";
                    $xml .= "    <lastmod>{$lastMod}</lastmod>\n";
                    $xml .= "    <changefreq>daily</changefreq>\n";
                    $xml .= "    <priority>0.88</priority>\n";
                    $xml .= "  </url>\n";
                }
            } catch (\Throwable $e) {}
        }

        // 6. Active & Approved Books
        if (Schema::hasTable('books')) {
            try {
                $books = Book::where('is_active', true)
                    ->where(function($q) {
                        $q->whereNull('mod_status')->orWhere('mod_status', 'approved');
                    })
                    ->latest('id')
                    ->take(5000)
                    ->get();

                foreach ($books as $b) {
                    $lastMod = $b->updated_at ? $b->updated_at->format('Y-m-d') : $now;
                    $slug = $b->slug ?: $b->id;
                    $loc = htmlspecialchars($baseUrl . '/books/' . $slug, ENT_XML1, 'UTF-8');
                    $xml .= "  <url>\n";
                    $xml .= "    <loc>{$loc}</loc>\n";
                    $xml .= "    <lastmod>{$lastMod}</lastmod>\n";
                    $xml .= "    <changefreq>daily</changefreq>\n";
                    $xml .= "    <priority>0.92</priority>\n";

                    if (!empty($b->cover_image)) {
                        $img = str_starts_with((string)$b->cover_image, 'http') ? $b->cover_image : $baseUrl . '/storage/' . ltrim((string)$b->cover_image, '/');
                        $xml .= "    <image:image>\n";
                        $xml .= "      <image:loc>" . htmlspecialchars($img, ENT_XML1, 'UTF-8') . "</image:loc>\n";
                        $xml .= "      <image:title>" . htmlspecialchars($b->title . ' — ' . ($b->author_name ?: 'আইডিয়া প্রকাশন'), ENT_XML1, 'UTF-8') . "</image:title>\n";
                        $xml .= "    </image:image>\n";
                    }

                    $xml .= "  </url>\n";
                }
            } catch (\Throwable $e) {}
        }

        // 7. Authors
        if (Schema::hasTable('authors')) {
            try {
                $authors = Author::where('is_active', true)->latest('id')->take(2000)->get();
                foreach ($authors as $a) {
                    $lastMod = $a->updated_at ? $a->updated_at->format('Y-m-d') : $now;
                    $slug = $a->slug ?: $a->id;
                    $loc = htmlspecialchars($baseUrl . '/authors/' . $slug, ENT_XML1, 'UTF-8');
                    $xml .= "  <url>\n";
                    $xml .= "    <loc>{$loc}</loc>\n";
                    $xml .= "    <lastmod>{$lastMod}</lastmod>\n";
                    $xml .= "    <changefreq>weekly</changefreq>\n";
                    $xml .= "    <priority>0.82</priority>\n";

                    if (!empty($a->avatar) || !empty($a->photo)) {
                        $img = str_starts_with((string)($a->avatar ?: $a->photo), 'http') ? ($a->avatar ?: $a->photo) : $baseUrl . '/storage/' . ltrim((string)($a->avatar ?: $a->photo), '/');
                        $xml .= "    <image:image>\n";
                        $xml .= "      <image:loc>" . htmlspecialchars($img, ENT_XML1, 'UTF-8') . "</image:loc>\n";
                        $xml .= "      <image:title>" . htmlspecialchars($a->name, ENT_XML1, 'UTF-8') . "</image:title>\n";
                        $xml .= "    </image:image>\n";
                    }

                    $xml .= "  </url>\n";
                }
            } catch (\Throwable $e) {}
        }

        // 8. Publishers
        if (Schema::hasTable('publishers')) {
            try {
                $publishers = Publisher::where('is_active', true)->latest('id')->take(1000)->get();
                foreach ($publishers as $p) {
                    $lastMod = $p->updated_at ? $p->updated_at->format('Y-m-d') : $now;
                    $slug = $p->slug ?: $p->id;
                    $loc = htmlspecialchars($baseUrl . '/publishers/' . $slug, ENT_XML1, 'UTF-8');
                    $xml .= "  <url>\n";
                    $xml .= "    <loc>{$loc}</loc>\n";
                    $xml .= "    <lastmod>{$lastMod}</lastmod>\n";
                    $xml .= "    <changefreq>weekly</changefreq>\n";
                    $xml .= "    <priority>0.80</priority>\n";

                    if (!empty($p->logo)) {
                        $img = str_starts_with((string)$p->logo, 'http') ? $p->logo : $baseUrl . '/storage/' . ltrim((string)$p->logo, '/');
                        $xml .= "    <image:image>\n";
                        $xml .= "      <image:loc>" . htmlspecialchars($img, ENT_XML1, 'UTF-8') . "</image:loc>\n";
                        $xml .= "      <image:title>" . htmlspecialchars($p->name . ' — প্রকাশনী', ENT_XML1, 'UTF-8') . "</image:title>\n";
                        $xml .= "    </image:image>\n";
                    }

                    $xml .= "  </url>\n";
                }
            } catch (\Throwable $e) {}
        }

        // 9. Ebooks
        if (Schema::hasTable('ebooks')) {
            try {
                $ebooks = Ebook::where('is_active', true)->latest('id')->take(1000)->get();
                foreach ($ebooks as $eb) {
                    $lastMod = $eb->updated_at ? $eb->updated_at->format('Y-m-d') : $now;
                    $slug = $eb->slug ?: $eb->id;
                    $loc = htmlspecialchars($baseUrl . '/ebooks/' . $slug, ENT_XML1, 'UTF-8');
                    $xml .= "  <url>\n";
                    $xml .= "    <loc>{$loc}</loc>\n";
                    $xml .= "    <lastmod>{$lastMod}</lastmod>\n";
                    $xml .= "    <changefreq>weekly</changefreq>\n";
                    $xml .= "    <priority>0.85</priority>\n";

                    if (!empty($eb->cover_image)) {
                        $img = str_starts_with((string)$eb->cover_image, 'http') ? $eb->cover_image : $baseUrl . '/storage/' . ltrim((string)$eb->cover_image, '/');
                        $xml .= "    <image:image>\n";
                        $xml .= "      <image:loc>" . htmlspecialchars($img, ENT_XML1, 'UTF-8') . "</image:loc>\n";
                        $xml .= "      <image:title>" . htmlspecialchars($eb->title, ENT_XML1, 'UTF-8') . "</image:title>\n";
                        $xml .= "    </image:image>\n";
                    }

                    $xml .= "  </url>\n";
                }
            } catch (\Throwable $e) {}
        }

        // 10. Webzines
        if (Schema::hasTable('webzines')) {
            try {
                $webzines = Webzine::where('is_active', true)->latest('id')->take(1000)->get();
                foreach ($webzines as $wz) {
                    $lastMod = $wz->updated_at ? $wz->updated_at->format('Y-m-d') : $now;
                    $slug = $wz->slug ?: $wz->id;
                    $loc = htmlspecialchars($baseUrl . '/webzines/' . $slug, ENT_XML1, 'UTF-8');
                    $xml .= "  <url>\n";
                    $xml .= "    <loc>{$loc}</loc>\n";
                    $xml .= "    <lastmod>{$lastMod}</lastmod>\n";
                    $xml .= "    <changefreq>monthly</changefreq>\n";
                    $xml .= "    <priority>0.82</priority>\n";

                    if (!empty($wz->cover_image)) {
                        $img = str_starts_with((string)$wz->cover_image, 'http') ? $wz->cover_image : $baseUrl . '/storage/' . ltrim((string)$wz->cover_image, '/');
                        $xml .= "    <image:image>\n";
                        $xml .= "      <image:loc>" . htmlspecialchars($img, ENT_XML1, 'UTF-8') . "</image:loc>\n";
                        $xml .= "      <image:title>" . htmlspecialchars($wz->title, ENT_XML1, 'UTF-8') . "</image:title>\n";
                        $xml .= "    </image:image>\n";
                    }

                    $xml .= "  </url>\n";
                }
            } catch (\Throwable $e) {}
        }

        // 11. Research Papers
        if (Schema::hasTable('research_papers')) {
            try {
                $papers = ResearchPaper::published()->latest('id')->take(1000)->get();
                foreach ($papers as $rp) {
                    $lastMod = $rp->updated_at ? $rp->updated_at->format('Y-m-d') : $now;
                    $slug = $rp->slug ?: $rp->id;
                    $loc = htmlspecialchars($baseUrl . '/research/' . $slug, ENT_XML1, 'UTF-8');
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
        $baseUrl = $this->getBaseUrl();
        $siteName = \App\Support\SiteSetting::name() ?: 'আইডিয়া প্রকাশন';
        $siteDesc = \App\Support\SiteSetting::tagline() ?: 'অনলাইন বই ও প্রকাশনা প্ল্যাটফর্ম';

        $posts = BlogPost::where('status', 'published')
            ->latest('published_at')
            ->take(50)
            ->get();

        $rss = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
        $rss .= '<rss version="2.0" xmlns:atom="http://www.w3.org/2005/Atom" xmlns:dc="http://purl.org/dc/elements/1.1/" xmlns:content="http://purl.org/rss/1.0/modules/content/">' . "\n";
        $rss .= "  <channel>\n";
        $rss .= "    <title>" . htmlspecialchars($siteName, ENT_XML1, 'UTF-8') . "</title>\n";
        $rss .= "    <link>{$baseUrl}</link>\n";
        $rss .= "    <description>" . htmlspecialchars($siteDesc, ENT_XML1, 'UTF-8') . "</description>\n";
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
            $rss .= "      <title>" . htmlspecialchars($post->title, ENT_XML1, 'UTF-8') . "</title>\n";
            $rss .= "      <link>{$postUrl}</link>\n";
            $rss .= "      <guid isPermaLink=\"true\">{$postUrl}</guid>\n";
            $rss .= "      <dc:creator>" . htmlspecialchars($author, ENT_XML1, 'UTF-8') . "</dc:creator>\n";
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

    /**
     * Ping Google and Bing Search Engines to immediately crawl updated sitemap.
     */
    public function pingSearchEngines(): Response
    {
        $baseUrl = $this->getBaseUrl();
        $sitemapUrl = urlencode($baseUrl . '/sitemap.xml');

        $results = [];

        // Ping Google
        try {
            $googleResp = Http::timeout(5)->get("https://www.google.com/ping?sitemap={$sitemapUrl}");
            $results['google'] = $googleResp->successful() ? 'success' : 'failed (' . $googleResp->status() . ')';
        } catch (\Throwable $e) {
            $results['google'] = 'error: ' . $e->getMessage();
        }

        // Ping Bing
        try {
            $bingResp = Http::timeout(5)->get("https://www.bing.com/ping?sitemap={$sitemapUrl}");
            $results['bing'] = $bingResp->successful() ? 'success' : 'failed (' . $bingResp->status() . ')';
        } catch (\Throwable $e) {
            $results['bing'] = 'error: ' . $e->getMessage();
        }

        return response()->json([
            'status' => 'completed',
            'sitemap_url' => $baseUrl . '/sitemap.xml',
            'results' => $results,
        ]);
    }
}
