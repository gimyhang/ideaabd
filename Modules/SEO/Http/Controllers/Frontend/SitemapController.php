<?php

namespace Modules\SEO\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Response;
use Modules\SEO\Services\SitemapService;

class SitemapController extends Controller
{
    public function __construct(protected SitemapService $sitemapService) {}

    public function sitemap(): Response
    {
        $xml = $this->sitemapService->generateIndexXml();
        return response($xml, 200, [
            'Content-Type'  => 'application/xml; charset=utf-8',
            'Cache-Control' => 'public, max-age=3600',
        ]);
    }

    public function robots(): Response
    {
        $txt = $this->sitemapService->generateRobotsTxt();
        return response($txt, 200, [
            'Content-Type'  => 'text/plain; charset=utf-8',
            'Cache-Control' => 'public, max-age=86400',
        ]);
    }
}
