<?php

namespace App\Support;

use DOMDocument;
use DOMElement;
use DOMNode;

/**
 * Allow-list HTML cleaner for text people write (blog posts, articles). Anything not explicitly allowed is removed:
 * scripts, frames, forms, event handlers (onclick, onerror…), javascript:/data: links, unsafe CSS. The result is safe
 * to print with {!! !!}. It is applied when content is saved and again when it is shown, so content that was stored
 * before this existed is cleaned too.
 */
class HtmlSanitizer
{
    /** Tags kept (children kept as well). */
    private const ALLOWED_TAGS = [
        'p', 'br', 'b', 'strong', 'i', 'em', 'u', 's', 'del', 'ins', 'sub', 'sup', 'small', 'mark', 'cite', 'q', 'abbr',
        'ul', 'ol', 'li', 'blockquote', 'pre', 'code', 'hr', 'span', 'div',
        'h1', 'h2', 'h3', 'h4', 'h5', 'h6', 'a', 'img', 'figure', 'figcaption',
        'table', 'thead', 'tbody', 'tfoot', 'tr', 'th', 'td', 'caption',
    ];

    /** Tags removed together with everything inside them. */
    private const DROP_WITH_CONTENT = [
        'script', 'style', 'iframe', 'frame', 'frameset', 'object', 'embed', 'applet', 'form', 'textarea', 'select', 'option',
        'button', 'input', 'link', 'meta', 'base', 'svg', 'math', 'template', 'noscript', 'audio', 'video', 'source', 'canvas', 'title', 'head',
    ];

    private const GLOBAL_ATTRS = ['class', 'title', 'lang', 'dir', 'style'];

    private const TAG_ATTRS = [
        'a'          => ['href', 'target', 'rel', 'hreflang'],
        'img'        => ['src', 'alt', 'width', 'height', 'loading'],
        'td'         => ['colspan', 'rowspan', 'align'],
        'th'         => ['colspan', 'rowspan', 'align', 'scope'],
        'ol'         => ['start', 'type'],
        'blockquote' => ['cite'],
        'q'          => ['cite'],
    ];

    private const SAFE_CSS = [
        'color', 'background-color', 'font-size', 'font-weight', 'font-style', 'font-family', 'text-align', 'text-decoration',
        'line-height', 'letter-spacing', 'margin', 'margin-top', 'margin-bottom', 'margin-left', 'margin-right',
        'padding', 'padding-top', 'padding-bottom', 'padding-left', 'padding-right', 'text-indent', 'white-space',
        'width', 'max-width', 'height', 'border', 'border-left', 'border-right', 'border-top', 'border-bottom', 'list-style-type',
    ];

    public static function clean(?string $html): string
    {
        $html = trim((string) $html);
        if ($html === '') {
            return '';
        }

        // Content is sometimes stored entity-encoded ("&lt;p&gt;"); decode once so it is cleaned as markup, never executed.
        if (!str_contains($html, '<') && preg_match('/&(lt|gt|quot|#\d+);/i', $html)) {
            $html = html_entity_decode($html, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        }

        $prev = libxml_use_internal_errors(true);
        $doc = new DOMDocument('1.0', 'UTF-8');
        $doc->loadHTML('<?xml encoding="UTF-8"><div id="__root">' . $html . '</div>', LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD | LIBXML_NOERROR | LIBXML_NOWARNING);
        libxml_clear_errors();
        libxml_use_internal_errors($prev);

        $root = $doc->getElementById('__root') ?? $doc->documentElement;
        if (!$root) {
            return e(strip_tags($html));
        }

        self::walk($root);

        $out = '';
        foreach (iterator_to_array($root->childNodes) as $child) {
            $out .= $doc->saveHTML($child);
        }

        return trim($out);
    }

    private static function walk(DOMNode $node): void
    {
        foreach (iterator_to_array($node->childNodes) as $child) {
            if ($child->nodeType === XML_COMMENT_NODE || $child->nodeType === XML_PI_NODE || $child->nodeType === XML_CDATA_SECTION_NODE) {
                $node->removeChild($child);
                continue;
            }
            if (!$child instanceof DOMElement) {
                continue;
            }

            $tag = strtolower($child->tagName);

            if (in_array($tag, self::DROP_WITH_CONTENT, true)) {
                $node->removeChild($child);
                continue;
            }

            if (!in_array($tag, self::ALLOWED_TAGS, true)) {
                // unknown tag: keep its (cleaned) children, lose the tag
                self::walk($child);
                while ($child->firstChild) {
                    $node->insertBefore($child->firstChild, $child);
                }
                $node->removeChild($child);
                continue;
            }

            self::cleanAttributes($child, $tag);
            self::walk($child);
        }
    }

    private static function cleanAttributes(DOMElement $el, string $tag): void
    {
        $allowed = array_merge(self::GLOBAL_ATTRS, self::TAG_ATTRS[$tag] ?? []);

        foreach (iterator_to_array($el->attributes) as $attr) {
            $name = strtolower($attr->nodeName);
            if (!in_array($name, $allowed, true)) {
                $el->removeAttribute($attr->nodeName);
                continue;
            }

            $value = $attr->nodeValue;

            if ($name === 'href' || $name === 'cite') {
                $safe = self::safeUrl($value, ['http', 'https', 'mailto', 'tel']);
                $safe === null ? $el->removeAttribute($attr->nodeName) : $el->setAttribute($attr->nodeName, $safe);
            } elseif ($name === 'src') {
                $safe = self::safeUrl($value, ['http', 'https'], true);
                $safe === null ? $el->removeAttribute($attr->nodeName) : $el->setAttribute($attr->nodeName, $safe);
            } elseif ($name === 'style') {
                $css = self::safeStyle($value);
                $css === '' ? $el->removeAttribute('style') : $el->setAttribute('style', $css);
            } elseif ($name === 'target') {
                in_array(strtolower($value), ['_blank', '_self'], true) ? null : $el->removeAttribute('target');
            } elseif (in_array($name, ['width', 'height', 'colspan', 'rowspan', 'start'], true)) {
                preg_match('/^\d{1,4}(%|px)?$/', trim($value)) ? null : $el->removeAttribute($attr->nodeName);
            }
        }

        if ($tag === 'a') {
            if (strtolower((string) $el->getAttribute('target')) === '_blank' || preg_match('~^https?://~i', $el->getAttribute('href'))) {
                $el->setAttribute('rel', 'noopener noreferrer nofollow ugc');
            }
        }
        if ($tag === 'img' && !$el->hasAttribute('loading')) {
            $el->setAttribute('loading', 'lazy');
        }
    }

    /** Returns the URL when its scheme is allowed (or it is relative / an in-page anchor), otherwise null. */
    private static function safeUrl(string $url, array $schemes, bool $allowDataImage = false): ?string
    {
        // Browsers ignore whitespace/control characters inside a scheme ("java\nscript:"): remove them before judging.
        $probe = strtolower(preg_replace('/[\x00-\x20\x{00A0}\x{200B}-\x{200F}\x{2028}\x{2029}\x{FEFF}]+/u', '', html_entity_decode($url, ENT_QUOTES | ENT_HTML5, 'UTF-8')) ?? '');
        $url = trim($url);

        if ($probe === '') {
            return null;
        }
        if ($allowDataImage && preg_match('~^data:image/(png|jpe?g|gif|webp|avif);base64,[a-z0-9+/=]+$~i', $probe)) {
            return $url;
        }
        if (preg_match('~^([a-z][a-z0-9+.\-]*):~', $probe, $m)) {
            return in_array($m[1], $schemes, true) ? $url : null;
        }
        // relative ("/path", "path", "#anchor", "?q"); "//host" is protocol-relative and allowed only when it is plain http(s)
        return $url;
    }

    private static function safeStyle(string $style): string
    {
        $out = [];
        foreach (explode(';', $style) as $decl) {
            if (!str_contains($decl, ':')) {
                continue;
            }
            [$prop, $val] = array_map('trim', explode(':', $decl, 2));
            $prop = strtolower($prop);
            $lower = strtolower($val);

            if (!in_array($prop, self::SAFE_CSS, true)) {
                continue;
            }
            if (preg_match('/(url\s*\(|expression|javascript:|vbscript:|behavior|binding|@import|\\\\|<|>|\/\*)/i', $lower)) {
                continue;
            }
            $out[] = $prop . ': ' . $val;
        }

        return implode('; ', $out);
    }
}
