<?php

namespace App\Support\Html;

use Symfony\Component\HtmlSanitizer\HtmlSanitizer;
use Symfony\Component\HtmlSanitizer\HtmlSanitizerConfig;

/**
 * Cleans post HTML before it is stored. The site renders post content as raw HTML,
 * so anything an author submits must be reduced to the formatting the rich-text
 * editor (TipTap StarterKit + Link + Image) produces. Scripts, event handlers
 * (onerror=…), iframes, styles and javascript:/data: URLs are all dropped.
 */
class PostHtml
{
    private static ?HtmlSanitizer $sanitizer = null;

    public static function clean(?string $html): ?string
    {
        if ($html === null) {
            return null;
        }

        return self::sanitizer()->sanitize($html);
    }

    /** Excerpts are plain text everywhere they are shown. */
    public static function plainText(?string $text): ?string
    {
        if ($text === null) {
            return null;
        }

        return trim(html_entity_decode(strip_tags($text), ENT_QUOTES | ENT_HTML5, 'UTF-8'));
    }

    /** Content as plain text for the search index: tags become spaces so words don't run together. */
    public static function searchText(?string $html): string
    {
        $text = html_entity_decode(strip_tags(str_replace('<', ' <', (string) $html)), ENT_QUOTES | ENT_HTML5, 'UTF-8');

        return trim(preg_replace('/\s+/u', ' ', $text));
    }

    private static function sanitizer(): HtmlSanitizer
    {
        if (self::$sanitizer) {
            return self::$sanitizer;
        }

        $config = (new HtmlSanitizerConfig())
            ->withMaxInputLength(1_000_000) // default is 20k chars, too short for long posts
            ->allowLinkSchemes(['http', 'https', 'mailto'])
            ->allowRelativeLinks()
            ->allowMediaSchemes(['http', 'https'])
            ->allowRelativeMedias()
            // Links that open elsewhere can't reach back into the page.
            ->forceAttribute('a', 'rel', 'noopener noreferrer nofollow');

        foreach (['p', 'br', 'hr', 'h1', 'h2', 'h3', 'h4', 'h5', 'h6', 'strong', 'b', 'em', 'i', 's', 'u',
                  'code', 'pre', 'blockquote', 'ul', 'ol', 'li', 'figure', 'figcaption'] as $tag) {
            $config = $config->allowElement($tag, ['class']);
        }

        $config = $config
            ->allowElement('a', ['href', 'title', 'target', 'class'])
            ->allowElement('img', ['src', 'alt', 'title', 'width', 'height', 'class']);

        return self::$sanitizer = new HtmlSanitizer($config);
    }
}
