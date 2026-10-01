<?php

namespace Modules\SEO\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class InstantIndexingService
{
    /**
     * Ping Google, Bing, and IndexNow to notify them of new/updated URLs or Sitemap.
     */
    public function pingAllSearchEngines(?string $sitemapUrl = null): array
    {
        $sitemap = $sitemapUrl ?: url('/sitemap.xml');
        $results = [];

        // 1. Ping Google Sitemap
        try {
            $googleUrl = 'https://www.google.com/ping?sitemap=' . urlencode($sitemap);
            $response = Http::timeout(5)->get($googleUrl);
            $results['google'] = [
                'success' => $response->successful() || $response->status() === 200,
                'status'  => $response->status(),
                'message' => 'গুগলে সাইটম্যাপ পিং সফল হয়েছে।',
            ];
        } catch (\Throwable $e) {
            $results['google'] = [
                'success' => false,
                'status'  => 500,
                'message' => 'গুগল পিং ত্রুটি: ' . $e->getMessage(),
            ];
        }

        // 2. Ping Bing Sitemap
        try {
            $bingUrl = 'https://www.bing.com/ping?sitemap=' . urlencode($sitemap);
            $response = Http::timeout(5)->get($bingUrl);
            $results['bing'] = [
                'success' => $response->successful() || $response->status() === 200,
                'status'  => $response->status(),
                'message' => 'বিং-এ সাইটম্যাপ পিং সফল হয়েছে।',
            ];
        } catch (\Throwable $e) {
            $results['bing'] = [
                'success' => false,
                'status'  => 500,
                'message' => 'বিং পিং ত্রুটি: ' . $e->getMessage(),
            ];
        }

        // 3. IndexNow Push (Bing, Yandex, Seznam)
        try {
            $host = parse_url(url('/'), PHP_URL_HOST);
            $indexNowPayload = [
                'host'        => $host,
                'key'         => md5($host . 'ideaabd_seo_key'),
                'keyLocation' => url('/indexnow-key.txt'),
                'urlList'     => [
                    url('/'),
                    url('/books'),
                    url('/blog'),
                    url('/ebooks'),
                    url('/webzines'),
                ]
            ];
            $response = Http::timeout(5)->post('https://api.indexnow.org/indexnow', $indexNowPayload);
            $results['indexnow'] = [
                'success' => $response->successful() || in_array($response->status(), [200, 202], true),
                'status'  => $response->status(),
                'message' => 'IndexNow প্রোটোকলে পিং রিকোয়েস্ট গৃহীত হয়েছে।',
            ];
        } catch (\Throwable $e) {
            $results['indexnow'] = [
                'success' => false,
                'status'  => 500,
                'message' => 'IndexNow ত্রুটি: ' . $e->getMessage(),
            ];
        }

        return $results;
    }

    /**
     * Submit single URL to IndexNow for instant crawling
     */
    public function submitUrl(string $url): bool
    {
        try {
            $host = parse_url($url, PHP_URL_HOST);
            $response = Http::timeout(5)->post('https://api.indexnow.org/indexnow', [
                'host'    => $host,
                'key'     => md5($host . 'ideaabd_seo_key'),
                'urlList' => [$url]
            ]);

            return $response->successful() || in_array($response->status(), [200, 202], true);
        } catch (\Throwable $e) {
            Log::warning('IndexNow single URL submission failed: ' . $e->getMessage());
            return false;
        }
    }
}
