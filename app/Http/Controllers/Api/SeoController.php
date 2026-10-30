<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Post;
use App\Models\Tenant;
use App\Support\Tenancy\CurrentTenant;
use Illuminate\Http\Response;
use Illuminate\Support\Str;

/**
 * Per-brand files for search engines and feed readers. The frontend host proxies
 * /sitemap.xml, /rss.xml and /robots.txt here (see vite.config.js), so each
 * brand's domain serves its own.
 */
class SeoController extends Controller
{
    private const SITEMAP_LIMIT = 5000;
    private const FEED_SIZE     = 20;

    private function tenant(): Tenant
    {
        return app(CurrentTenant::class)->get();
    }

    public function sitemap(): Response
    {
        $base  = $this->tenant()->publicUrl();
        $posts = Post::live()->latest('published_at')->take(self::SITEMAP_LIMIT)->get(['slug', 'updated_at', 'published_at']);

        $urls = [
            ['loc' => "{$base}/", 'lastmod' => $posts->first()?->updated_at, 'priority' => '1.0'],
            ['loc' => "{$base}/blog", 'lastmod' => $posts->first()?->updated_at, 'priority' => '0.8'],
            ['loc' => "{$base}/about", 'lastmod' => null, 'priority' => '0.5'],
        ];
        foreach (Category::orderBy('name')->get(['slug']) as $cat) {
            $urls[] = ['loc' => "{$base}/blog?category=".rawurlencode($cat->slug), 'lastmod' => null, 'priority' => '0.6'];
        }
        foreach ($posts as $post) {
            $urls[] = ['loc' => "{$base}/blog/".rawurlencode($post->slug), 'lastmod' => $post->updated_at, 'priority' => '0.7'];
        }

        $xml = '<?xml version="1.0" encoding="UTF-8"?>'."\n"
            .'<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">'."\n";
        foreach ($urls as $u) {
            $xml .= '  <url><loc>'.e($u['loc']).'</loc>'
                .($u['lastmod'] ? '<lastmod>'.$u['lastmod']->toAtomString().'</lastmod>' : '')
                .'<priority>'.$u['priority'].'</priority></url>'."\n";
        }
        $xml .= '</urlset>'."\n";

        return $this->xml($xml, 'application/xml');
    }

    public function rss(): Response
    {
        $tenant = $this->tenant();
        $base   = $tenant->publicUrl();
        $theme  = $tenant->settings['theme'] ?? [];
        $posts  = Post::live()->with('user:id,name')->latest('published_at')->take(self::FEED_SIZE)->get();

        $xml = '<?xml version="1.0" encoding="UTF-8"?>'."\n"
            .'<rss version="2.0" xmlns:atom="http://www.w3.org/2005/Atom" xmlns:dc="http://purl.org/dc/elements/1.1/">'."\n"
            .'<channel>'."\n"
            .'  <title>'.e($tenant->name).'</title>'."\n"
            .'  <link>'.e("{$base}/").'</link>'."\n"
            .'  <description>'.e($theme['about'] ?? $theme['tagline'] ?? "Latest posts from {$tenant->name}").'</description>'."\n"
            .'  <atom:link href="'.e("{$base}/rss.xml").'" rel="self" type="application/rss+xml" />'."\n"
            .($posts->first() ? '  <lastBuildDate>'.$posts->first()->published_at->toRssString().'</lastBuildDate>'."\n" : '');

        foreach ($posts as $post) {
            $link    = "{$base}/blog/".rawurlencode($post->slug);
            $summary = $post->meta_description ?: $post->excerpt ?: Str::limit(trim(strip_tags($post->content)), 300);
            $xml .= '  <item>'."\n"
                .'    <title>'.e($post->title).'</title>'."\n"
                .'    <link>'.e($link).'</link>'."\n"
                .'    <guid isPermaLink="true">'.e($link).'</guid>'."\n"
                .'    <pubDate>'.$post->published_at->toRssString().'</pubDate>'."\n"
                .($post->user ? '    <dc:creator>'.e($post->user->name).'</dc:creator>'."\n" : '')
                .'    <description>'.e($summary).'</description>'."\n"
                .'  </item>'."\n";
        }
        $xml .= '</channel>'."\n".'</rss>'."\n";

        return $this->xml($xml, 'application/rss+xml');
    }

    public function robots(): Response
    {
        $base = $this->tenant()->publicUrl();

        return response(
            "User-agent: *\nAllow: /\nDisallow: /admin\nDisallow: /user\nDisallow: /invite/\n\nSitemap: {$base}/sitemap.xml\n",
            200,
            ['Content-Type' => 'text/plain; charset=UTF-8'],
        );
    }

    private function xml(string $body, string $type): Response
    {
        return response($body, 200, [
            'Content-Type'  => "{$type}; charset=UTF-8",
            'Cache-Control' => 'public, max-age=900',
        ]);
    }
}
