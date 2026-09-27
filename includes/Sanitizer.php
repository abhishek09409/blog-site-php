<?php

declare(strict_types=1);

class Sanitizer
{
    private const ALLOWED = [
        'p' => [],
        'br' => [],
        'h2' => [],
        'h3' => [],
        'h4' => [],
        'strong' => [],
        'em' => [],
        'ul' => [],
        'ol' => [],
        'li' => [],
        'blockquote' => [],
        'pre' => [],
        'code' => ['class'],
        'a' => ['href', 'title'],
        'img' => ['src', 'alt', 'width', 'height'],
        'table' => [],
        'thead' => [],
        'tbody' => [],
        'tr' => [],
        'th' => ['colspan', 'rowspan'],
        'td' => ['colspan', 'rowspan'],
        'figure' => [],
        'figcaption' => [],
        'hr' => [],
        'div' => ['class'],
        'span' => [],
        'iframe' => ['src', 'title', 'width', 'height', 'allow', 'allowfullscreen', 'loading'],
    ];

    private const DROP = ['script', 'style', 'object', 'embed', 'form', 'input', 'textarea', 'button', 'link', 'meta', 'base', 'svg', 'math'];

    public static function html(string $dirty): string
    {
        $dirty = preg_replace('#<(script|style)\b[^>]*>.*?</\1>#is', '', $dirty) ?? '';
        $document = new DOMDocument('1.0', 'UTF-8');
        $previous = libxml_use_internal_errors(true);
        $document->loadHTML('<?xml encoding="utf-8"><body>' . $dirty . '</body>', LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
        libxml_clear_errors();
        libxml_use_internal_errors($previous);
        $body = $document->getElementsByTagName('body')->item(0);
        if (!$body) {
            return '';
        }
        self::clean($body);
        $html = '';
        foreach ($body->childNodes as $child) {
            $html .= $document->saveHTML($child);
        }
        return trim($html);
    }

    private static function clean(DOMNode $node): void
    {
        if (!$node->hasChildNodes()) {
            return;
        }
        for ($i = $node->childNodes->length - 1; $i >= 0; $i--) {
            $child = $node->childNodes->item($i);
            if (!$child) {
                continue;
            }
            if ($child instanceof DOMElement) {
                $tag = strtolower($child->tagName);
                if (in_array($tag, self::DROP, true)) {
                    $node->removeChild($child);
                    continue;
                }
                if (!isset(self::ALLOWED[$tag])) {
                    self::unwrap($child);
                    continue;
                }
                self::filterAttributes($child, $tag);
                if ($tag === 'a' && !$child->hasAttribute('href')) {
                    self::unwrap($child);
                    continue;
                }
                if ($tag === 'img' && !$child->hasAttribute('src')) {
                    $node->removeChild($child);
                    continue;
                }
                if ($tag === 'iframe' && !self::safeYouTube($child->getAttribute('src'))) {
                    $node->removeChild($child);
                    continue;
                }
                self::clean($child);
            } elseif ($child instanceof DOMComment) {
                $node->removeChild($child);
            }
        }
    }

    private static function unwrap(DOMElement $element): void
    {
        $parent = $element->parentNode;
        if (!$parent) {
            return;
        }
        while ($element->firstChild) {
            $parent->insertBefore($element->firstChild, $element);
        }
        $parent->removeChild($element);
        self::clean($parent);
    }

    private static function filterAttributes(DOMElement $element, string $tag): void
    {
        $allowed = self::ALLOWED[$tag];
        $attrs = [];
        foreach ($element->attributes ?? [] as $attribute) {
            $attrs[] = $attribute->name;
        }
        foreach ($attrs as $name) {
            $value = trim($element->getAttribute($name));
            $keep = in_array(strtolower($name), $allowed, true) && !str_starts_with(strtolower($name), 'on');
            if ($keep && $tag === 'a' && $name === 'href') {
                $keep = self::safeHref($value);
            }
            if ($keep && $tag === 'img' && $name === 'src') {
                $keep = self::safeImage($value);
            }
            if ($keep && $tag === 'code' && $name === 'class') {
                $keep = (bool) preg_match('/^language-[a-z0-9_+-]+$/', $value);
            }
            if ($keep && $tag === 'div' && $name === 'class') {
                $keep = in_array($value, ['embed', 'table-wrap'], true);
            }
            if ($keep && in_array($name, ['width', 'height', 'colspan', 'rowspan'], true)) {
                $keep = ctype_digit($value) && (int) $value > 0 && (int) $value < 50;
            }
            if (!$keep) {
                $element->removeAttribute($name);
            }
        }
        if ($tag === 'a' && $element->hasAttribute('href')) {
            $href = $element->getAttribute('href');
            if (preg_match('#^https?://#i', $href)) {
                $element->setAttribute('rel', 'noopener noreferrer');
                $element->setAttribute('target', '_blank');
            }
        }
        if ($tag === 'iframe') {
            $element->setAttribute('loading', 'lazy');
            $element->setAttribute('referrerpolicy', 'strict-origin-when-cross-origin');
            $element->removeAttribute('allowfullscreen');
            $element->setAttribute('allowfullscreen', 'allowfullscreen');
        }
    }

    private static function safeHref(string $href): bool
    {
        if (preg_match('/[\x00-\x1F]/', $href)) {
            return false;
        }
        if (str_starts_with($href, '/') && !str_starts_with($href, '//')) {
            return true;
        }
        if (str_starts_with(strtolower($href), 'mailto:')) {
            return true;
        }
        return safe_url($href) !== null;
    }

    private static function safeImage(string $src): bool
    {
        if (preg_match('/[\x00-\x1F]/', $src) || preg_match('/\.svg($|\?)/i', $src)) {
            return false;
        }
        if (str_starts_with($src, '/uploads/') || str_starts_with($src, '/assets/')) {
            return !str_contains($src, '..');
        }
        return safe_url($src) !== null;
    }

    private static function safeYouTube(string $src): bool
    {
        return (bool) preg_match('#^https://www\.youtube(?:-nocookie)?\.com/embed/[A-Za-z0-9_-]{6,20}$#', $src);
    }
}
