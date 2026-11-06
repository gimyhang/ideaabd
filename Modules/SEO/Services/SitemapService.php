<?php

namespace Modules\SEO\Services;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Response;
use Modules\SEO\Models\SeoMeta;

class SitemapService
{
    protected function getBaseUrl(): string
    {
        $baseUrl = config('app.url', 'https://www.ideaabd.com');
        if (str_contains($baseUrl, 'localhost') || str_contains($baseUrl, '127.0.0.1')) {
            $baseUrl = 'https://www.ideaabd.com';
        }
        return rtrim($baseUrl, '/');
    }

    /**
     * Generate Main XML Sitemap Index or Full Sitemap
     */
    public function generateIndexXml(): string
    {
        $urls = [];
        $baseUrl = $this->getBaseUrl();

        // Core Static Pages
        $staticPaths = ['/', '/books', '/ebooks', '/authors', '/publishers', '/blog', '/webzines', '/research', '/about', '/contact', '/terms', '/hub'];
        foreach ($staticPaths as $p) {
            $urls[] = [
                'loc'        => $baseUrl . ($p === '/' ? '/' : '/' . ltrim($p, '/')),
                'lastmod'    => now()->toDateString(),
                'changefreq' => ($p === '/' ? 'daily' : 'weekly'),
                'priority'   => ($p === '/' ? '1.0' : '0.8'),
            ];
        }

        // Books
        if (class_exists(\Modules\Book\Models\Book::class)) {
            $books = \Modules\Book\Models\Book::where('is_active', true)->latest('updated_at')->take(2000)->get(['id', 'slug', 'updated_at']);
            foreach ($books as $b) {
                $urls[] = [
                    'loc'        => $baseUrl . '/books/' . ($b->slug ?: $b->id),
                    'lastmod'    => $b->updated_at ? $b->updated_at->toDateString() : now()->toDateString(),
                    'changefreq' => 'weekly',
                    'priority'   => '0.9',
                ];
            }
        }

        // Blog Posts
        if (class_exists(\Modules\Blog\Models\BlogPost::class)) {
            $posts = \Modules\Blog\Models\BlogPost::where('status', 'published')->latest('updated_at')->take(2000)->get(['id', 'slug', 'updated_at']);
            foreach ($posts as $post) {
                $urls[] = [
                    'loc'        => $baseUrl . '/blog/' . ($post->slug ?: $post->id),
                    'lastmod'    => $post->updated_at ? $post->updated_at->toDateString() : now()->toDateString(),
                    'changefreq' => 'weekly',
                    'priority'   => '0.8',
                ];
            }
        }

        // Authors
        if (class_exists(\Modules\Author\Models\Author::class)) {
            $authors = \Modules\Author\Models\Author::where('is_active', true)->latest('updated_at')->take(1000)->get(['id', 'slug', 'updated_at']);
            foreach ($authors as $author) {
                $urls[] = [
                    'loc'        => $baseUrl . '/authors/' . ($author->slug ?: $author->id),
                    'lastmod'    => $author->updated_at ? $author->updated_at->toDateString() : now()->toDateString(),
                    'changefreq' => 'monthly',
                    'priority'   => '0.7',
                ];
            }
        }

        // Webzines
        if (class_exists(\Modules\Webzine\Models\Webzine::class)) {
            $webzines = \Modules\Webzine\Models\Webzine::latest('updated_at')->take(500)->get(['id', 'slug', 'updated_at']);
            foreach ($webzines as $wz) {
                $urls[] = [
                    'loc'        => $baseUrl . '/webzines/' . ($wz->slug ?: $wz->id),
                    'lastmod'    => $wz->updated_at ? $wz->updated_at->toDateString() : now()->toDateString(),
                    'changefreq' => 'monthly',
                    'priority'   => '0.7',
                ];
            }
        }

        // Ebooks
        if (class_exists(\Modules\Ebook\Models\Ebook::class)) {
            $ebooks = \Modules\Ebook\Models\Ebook::latest('updated_at')->take(1000)->get(['id', 'slug', 'updated_at']);
            foreach ($ebooks as $eb) {
                $urls[] = [
                    'loc'        => $baseUrl . '/ebooks/' . ($eb->slug ?: $eb->id),
                    'lastmod'    => $eb->updated_at ? $eb->updated_at->toDateString() : now()->toDateString(),
                    'changefreq' => 'weekly',
                    'priority'   => '0.8',
                ];
            }
        }

        // Publishers
        if (class_exists(\Modules\Publisher\Models\Publisher::class)) {
            $publishers = \Modules\Publisher\Models\Publisher::latest('updated_at')->take(500)->get(['id', 'slug', 'updated_at']);
            foreach ($publishers as $pub) {
                $urls[] = [
                    'loc'        => $baseUrl . '/publishers/' . ($pub->slug ?: $pub->id),
                    'lastmod'    => $pub->updated_at ? $pub->updated_at->toDateString() : now()->toDateString(),
                    'changefreq' => 'monthly',
                    'priority'   => '0.7',
                ];
            }
        }

        // Research Papers
        if (class_exists(\Modules\Research\Models\ResearchPaper::class)) {
            $papers = \Modules\Research\Models\ResearchPaper::where('is_published', true)->latest('updated_at')->take(1000)->get(['id', 'slug', 'updated_at']);
            foreach ($papers as $paper) {
                $urls[] = [
                    'loc'        => $baseUrl . '/research/' . ($paper->slug ?: $paper->id),
                    'lastmod'    => $paper->updated_at ? $paper->updated_at->toDateString() : now()->toDateString(),
                    'changefreq' => 'monthly',
                    'priority'   => '0.7',
                ];
            }
        }

        // Build XML
        $xml = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
        $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9"' . "\n";
        $xml .= '        xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"' . "\n";
        $xml .= '        xmlns:image="http://www.google.com/schemas/sitemap-image/1.1">' . "\n";

        foreach ($urls as $u) {
            $xml .= "  <url>\n";
            $xml .= "    <loc>" . htmlspecialchars($u['loc'], ENT_XML1, 'UTF-8') . "</loc>\n";
            $xml .= "    <lastmod>" . $u['lastmod'] . "</lastmod>\n";
            $xml .= "    <changefreq>" . $u['changefreq'] . "</changefreq>\n";
            $xml .= "    <priority>" . $u['priority'] . "</priority>\n";
            $xml .= "  </url>\n";
        }

        $xml .= '</urlset>';

        return $xml;
    }

    /**
     * Generate dynamic robots.txt
     */
    public function generateRobotsTxt(): string
    {
        $sitemapUrl = $this->getBaseUrl() . '/sitemap.xml';
        return "User-agent: *\n"
            . "Allow: /\n"
            . "Disallow: /admin/\n"
            . "Disallow: /admin\n"
            . "Disallow: /api/\n"
            . "Disallow: /cart\n"
            . "Disallow: /checkout\n"
            . "Disallow: /account\n"
            . "Disallow: /storage/temp/\n\n"
            . "Sitemap: {$sitemapUrl}\n";
    }
}
