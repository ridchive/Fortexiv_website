<?php

namespace App\Services;

use DOMDocument;
use DOMElement;
use DOMNode;

class TermsContentSanitizer
{
    private const ALLOWED_TAGS = ['a', 'b', 'br', 'div', 'em', 'i', 'li', 'ol', 'p', 'strong', 'ul'];

    private const DROP_CONTENT_TAGS = ['embed', 'iframe', 'object', 'script', 'style', 'svg', 'template'];

    public function sanitize(string $content): string
    {
        $document = new DOMDocument('1.0', 'UTF-8');
        $previousErrorSetting = libxml_use_internal_errors(true);

        try {
            $document->loadHTML(
                '<?xml encoding="UTF-8"><div id="terms-content">'.$content.'</div>',
                LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD | LIBXML_NONET,
            );

            $container = $document->getElementById('terms-content');
            if (! $container) {
                return '';
            }

            $this->sanitizeChildren($container);

            $html = '';
            foreach ($container->childNodes as $child) {
                $html .= $document->saveHTML($child);
            }

            return trim($html);
        } finally {
            libxml_clear_errors();
            libxml_use_internal_errors($previousErrorSetting);
        }
    }

    private function sanitizeChildren(DOMNode $node): void
    {
        foreach (iterator_to_array($node->childNodes) as $child) {
            if (! $child instanceof DOMElement) {
                if ($child->nodeType === XML_COMMENT_NODE) {
                    $node->removeChild($child);
                }

                continue;
            }

            $tag = strtolower($child->tagName);
            if (in_array($tag, self::DROP_CONTENT_TAGS, true)) {
                $node->removeChild($child);

                continue;
            }

            $this->sanitizeChildren($child);

            if (! in_array($tag, self::ALLOWED_TAGS, true)) {
                while ($child->firstChild) {
                    $node->insertBefore($child->firstChild, $child);
                }
                $node->removeChild($child);

                continue;
            }

            foreach (iterator_to_array($child->attributes) as $attribute) {
                if ($tag !== 'a' || $attribute->name !== 'href' || ! $this->isSafeLink($attribute->value)) {
                    $child->removeAttribute($attribute->name);
                }
            }
        }
    }

    private function isSafeLink(string $url): bool
    {
        $url = trim($url);

        if ($url === '' || str_starts_with($url, '//')) {
            return false;
        }

        if (str_starts_with($url, '/') || str_starts_with($url, '#')) {
            return true;
        }

        return in_array(strtolower((string) parse_url($url, PHP_URL_SCHEME)), ['http', 'https', 'mailto'], true);
    }
}
