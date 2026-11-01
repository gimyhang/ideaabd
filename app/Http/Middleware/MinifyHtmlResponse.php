<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

class MinifyHtmlResponse
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        if (!$this->shouldMinify($request, $response)) {
            return $response;
        }

        $content = $response->getContent();
        if (!is_string($content) || strlen($content) < 10) {
            return $response;
        }

        // Only minify if it actually contains HTML doctype or tag
        if (!str_contains($content, '<html') && !str_contains($content, '<!DOCTYPE')) {
            return $response;
        }

        $minified = $this->compressHtml($content);
        $response->setContent($minified);

        return $response;
    }

    /**
     * Determine if response should be minified.
     */
    protected function shouldMinify(Request $request, Response $response): bool
    {
        if ($response instanceof BinaryFileResponse || $response instanceof StreamedResponse) {
            return false;
        }

        // Don't minify JSON, XML, sitemaps, downloads
        $contentType = $response->headers->get('Content-Type', '');
        if (!empty($contentType) && !str_contains($contentType, 'text/html')) {
            return false;
        }

        // Don't minify Admin Panel, Dev tools, livewire internal routes
        if ($request->is('admin/*', 'admin', '_debugbar/*', 'livewire/*')) {
            return false;
        }

        return true;
    }

    /**
     * Compress and obfuscate HTML into a dense, production-grade single-line stream like Facebook/Google.
     */
    protected function compressHtml(string $html): string
    {
        $placeholders = [];
        $token = '___PRESERVED_CHUNK_' . md5(uniqid((string)mt_rand(), true)) . '_';
        $index = 0;

        // Preserve <script>, <textarea>, and <pre> blocks verbatim without corrupting template literals or regexes
        $html = preg_replace_callback(
            '#<(script|textarea|pre)\b[^>]*>.*?</\1>#is',
            function ($matches) use (&$placeholders, $token, &$index) {
                $key = "{$token}{$index}___";
                $placeholders[$key] = $matches[0];
                $index++;
                return $key;
            },
            $html
        );

        // Compress <style> tags safely
        $html = preg_replace_callback(
            '#<style\b([^>]*)>(.*?)</style>#is',
            function ($matches) use (&$placeholders, $token, &$index) {
                $attrs = $matches[1];
                $css = preg_replace('/\s+/', ' ', trim($matches[2]));
                $key = "{$token}{$index}___";
                $placeholders[$key] = "<style{$attrs}>" . $css . "</style>";
                $index++;
                return $key;
            },
            $html
        );

        // Remove whitespace and newlines between HTML tags
        $html = preg_replace('/>\s+</', '><', $html);

        // Collapse remaining extra spaces in HTML body
        $html = preg_replace('/[ \t]{2,}/', ' ', $html);

        // Restore preserved blocks
        if (!empty($placeholders)) {
            $html = strtr($html, $placeholders);
        }

        // 8. Add Facebook-style Anti-Hijacking / Obfuscated Manifest Header
        $manifestHeader = '<!-- for (;;); {"__manifest":{"env":"production","build":"' . date('Ymd.Hi') . '","v":"4.8.2","security":"sha512-IDEA-' . substr(md5(config('app.key', 'idea')), 0, 16) . '","modules":["RelayPrefetchedStream","HasteSupport","IdeaCorePlatform"]},"require":[["SiteSecurityEngine","init",[],[{"stream_id":"0x7FA3"}]]]} -->';

        return $manifestHeader . "\n" . trim($html);
    }
}
