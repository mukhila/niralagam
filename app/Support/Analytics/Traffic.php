<?php

namespace App\Support\Analytics;

use Illuminate\Support\Str;

/** Classifies a page view's traffic source and device. */
class Traffic
{
    public const SOURCES = ['direct', 'search', 'social', 'newsletter', 'referral'];

    private const SEARCH = ['google.', 'bing.com', 'duckduckgo.com', 'yahoo.', 'yandex.', 'baidu.com', 'ecosia.org', 'search.brave.com'];
    private const SOCIAL = ['facebook.com', 'fb.me', 'm.facebook.com', 'l.facebook.com', 'instagram.com', 't.co', 'twitter.com', 'x.com',
        'linkedin.com', 'lnkd.in', 'whatsapp.com', 'wa.me', 'reddit.com', 'pinterest.', 'youtube.com', 't.me', 'telegram.',
        'threads.net', 'bsky.app', 'mastodon.', 'quora.com'];
    private const EMAIL_UTM = ['newsletter', 'email', 'follow-alert'];

    private const BOTS = '/bot|crawl|spider|slurp|preview|facebookexternalhit|headless|lighthouse|monitor|curl|wget|python-requests/i';

    public static function isBot(?string $userAgent): bool
    {
        return $userAgent === null || $userAgent === '' || preg_match(self::BOTS, $userAgent) === 1;
    }

    /**
     * @return array{0:string,1:?string} [source, referrer host]
     */
    public static function source(?string $referrer, ?string $utmSource, ?string $ownHost): array
    {
        $utm = Str::lower((string) $utmSource);
        if (in_array($utm, self::EMAIL_UTM, true)) {
            return ['newsletter', null];
        }

        $host = $referrer ? Str::lower((string) parse_url($referrer, PHP_URL_HOST)) : '';
        $host = preg_replace('/^www\./', '', $host);

        // No referrer, or a click within the same site, counts as direct.
        if ($host === '' || ($ownHost && $host === preg_replace('/^www\./', '', Str::lower($ownHost)))) {
            return [$utm !== '' ? 'referral' : 'direct', $utm !== '' ? Str::limit($utm, 100, '') : null];
        }

        $matches = fn (array $list) => collect($list)->contains(fn ($d) => $host === rtrim($d, '.') || str_contains($host, $d));

        return match (true) {
            $matches(self::SEARCH) => ['search', $host],
            $matches(self::SOCIAL) => ['social', $host],
            default                => ['referral', $host],
        };
    }

    public static function device(?string $userAgent): string
    {
        $ua = (string) $userAgent;

        return match (true) {
            (bool) preg_match('/iPad|Tablet|PlayBook|Silk|(Android(?!.*Mobile))/i', $ua) => 'tablet',
            (bool) preg_match('/Mobi|iPhone|iPod|Android.*Mobile|Opera Mini|IEMobile/i', $ua) => 'mobile',
            default => 'desktop',
        };
    }
}
