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

        // Don't minify dev tools / livewire internal routes
        if ($request->is('_debugbar/*', 'livewire/*')) {
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

        // 1. Preserve <textarea> and <pre> tags EXACTLY as they are
        $html = preg_replace_callback(
            '#<(textarea|pre)\b[^>]*>.*?</\1>#is',
            function ($matches) use (&$placeholders, $token, &$index) {
                $key = "{$token}{$index}___";
                $placeholders[$key] = $matches[0];
                $index++;
                return $key;
            },
            $html
        );

        // 2. Preserve and safely compress <script> tags without breaking JS lines
        $html = preg_replace_callback(
            '#<script\b([^>]*)>(.*?)</script>#is',
            function ($matches) use (&$placeholders, $token, &$index) {
                $attrs = $matches[1];
                $js = $matches[2];

                // JSON-LD or templates - keep intact
                if (preg_match('#type=["\'](?:application\/ld\+json|text\/template)["\']#i', $attrs)) {
                    $key = "{$token}{$index}___";
                    $placeholders[$key] = $matches[0];
                    $index++;
                    return $key;
                }

                $trimmedJs = trim($js);
                if ($trimmedJs !== '') {
                    // Remove multi-line comments /* ... */
                    $trimmedJs = preg_replace('#/\*(?![\!]).*?\*/#s', '', $trimmedJs);

                    // Safely process lines: trim each line and remove standalone single-line comments
                    $lines = explode("\n", $trimmedJs);
                    $cleaned = [];
                    foreach ($lines as $line) {
                        $tLine = trim($line);
                        // Skip pure comments
                        if (str_starts_with($tLine, '//')) {
                            continue;
                        }
                        if ($tLine !== '') {
                            $cleaned[] = $tLine;
                        }
                    }
                    $trimmedJs = implode("\n", $cleaned);
                }

                $key = "{$token}{$index}___";
                $placeholders[$key] = "<script{$attrs}>" . $trimmedJs . "</script>";
                $index++;
                return $key;
            },
            $html
        );

        // 3. Compress <style> tags
        $html = preg_replace_callback(
            '#<style\b([^>]*)>(.*?)</style>#is',
            function ($matches) use (&$placeholders, $token, &$index) {
                $attrs = $matches[1];
                $css = $matches[2];
                // Strip CSS comments
                $css = preg_replace('#/\*.*?\*/#s', '', $css);
                // Collapse whitespaces
                $css = preg_replace('/\s+/', ' ', $css);
                $css = preg_replace('/\s*([{}|:;,>~+])\s*/', '$1', $css);
                $css = trim($css);

                $key = "{$token}{$index}___";
                $placeholders[$key] = "<style{$attrs}>" . $css . "</style>";
                $index++;
                return $key;
            },
            $html
        );

        // 4. Remove standard HTML comments (keep IE conditionals if any)
        $html = preg_replace('/<!--(?!\s*(?:\[if [^\]]+]|<!|>))(?:(?!-->).)*-->/s', '', $html);

        // 5. Remove whitespace and newlines between HTML tags (Facebook style)
        $html = preg_replace('/>\s+</', '><', $html);

        // 6. Collapse remaining extra whitespaces in HTML body
        $html = preg_replace('/[ \t]+/', ' ', $html);

        // 7. Restore preserved blocks
        if (!empty($placeholders)) {
            $html = strtr($html, $placeholders);
        }

        // 8. Add Facebook-style Anti-Hijacking / Obfuscated Manifest Header
        $manifestHeader = '<!-- for (;;); {"__manifest":{"env":"production","build":"' . date('Ymd.Hi') . '","v":"4.8.2","security":"sha512-IDEA-' . substr(md5(config('app.key', 'idea')), 0, 16) . '","modules":["RelayPrefetchedStream","HasteSupport","IdeaCorePlatform"]},"require":[["SiteSecurityEngine","init",[],[{"stream_id":"0x7FA3"}]]]} -->';

        return $manifestHeader . "\n" . trim($html);
    }
}
