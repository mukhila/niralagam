<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Post;
use App\Models\Tenant;
use App\Support\Tenancy\CurrentTenant;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Str;

/**
 * Link-preview bots (WhatsApp, Facebook, X, LinkedIn, Slack, Telegram, Discord…)
 * don't run JavaScript, so they can't see the tags the React app sets. The web
 * server sends those bots here instead (vite.config.js in dev, docs/SHARE_PREVIEWS.md
 * in production), and this returns a tiny HTML page with just the share tags.
 *
 * GET /api/share?path=/blog/{slug}  — a post's card
 * GET /api/share?path=/ | /blog | /about — the brand's card
 */
class SharePreviewController extends Controller
{
    public function show(Request $request): Response
    {
        $path   = '/'.ltrim((string) $request->query('path', '/'), '/');
        $tenant = app(CurrentTenant::class)->get();
        $base   = $tenant->publicUrl();
        $theme  = $tenant->settings['theme'] ?? [];

        $card = [
            'title'       => $tenant->name,
            'description' => $theme['about'] ?? $theme['tagline'] ?? "Stories from {$tenant->name}",
            'image'       => $tenant->logo,
            'url'         => $base.'/',
            'type'        => 'website',
            'article'     => null,
        ];
        $status = 200;

        if (preg_match('#^/blog/([^/]+)/?$#', $path, $m)) {
            $post = Post::live()->with(['user:id,name', 'category:id,name'])->where('slug', rawurldecode($m[1]))->first();

            if ($post) {
                $card = [
                    'title'       => $post->meta_title ?: $post->title,
                    'description' => $post->meta_description ?: $post->excerpt ?: Str::limit(trim(preg_replace('/\s+/', ' ', strip_tags($post->content))), 160),
                    'image'       => $post->og_image ?: $post->featured_image ?: $tenant->logo,
                    'url'         => $base.'/blog/'.rawurlencode($post->slug),
                    'type'        => 'article',
                    'locale'      => $post->language,
                    'article'     => [
                        'published_time' => $post->published_at?->toIso8601String(),
                        'modified_time'  => $post->updated_at?->toIso8601String(),
                        'author'         => $post->user?->name,
                        'section'        => $post->category?->name,
                    ],
                ];
            } else {
                $status = 404; // unknown or unpublished: the brand's card, never the draft's content
            }
        } elseif (preg_match('#^/(blog|about)/?$#', $path, $m)) {
            $card['title'] = ($m[1] === 'blog' ? 'Blog' : 'About')." · {$tenant->name}";
            $card['url']   = "{$base}/{$m[1]}";
        }

        return response($this->html($card, $tenant), $status, [
            'Content-Type'  => 'text/html; charset=UTF-8',
            'Cache-Control' => 'public, max-age=600',
        ]);
    }

    private function html(array $c, Tenant $tenant): string
    {
        $meta = fn (string $attr, string $key, ?string $value) => $value === null || $value === ''
            ? ''
            : '    <meta '.$attr.'="'.e($key).'" content="'.e($value).'">'."\n";

        $tags = $meta('property', 'og:site_name', $tenant->name)
            .$meta('property', 'og:locale', $c['locale'] ?? $tenant->languages()['default'])
            .$meta('property', 'og:type', $c['type'])
            .$meta('property', 'og:title', $c['title'])
            .$meta('property', 'og:description', $c['description'])
            .$meta('property', 'og:url', $c['url'])
            .$meta('property', 'og:image', $c['image'])
            .$meta('name', 'twitter:card', $c['image'] ? 'summary_large_image' : 'summary')
            .$meta('name', 'twitter:title', $c['title'])
            .$meta('name', 'twitter:description', $c['description'])
            .$meta('name', 'twitter:image', $c['image'])
            .$meta('name', 'description', $c['description']);

        foreach ($c['article'] ?? [] as $key => $value) {
            $tags .= $meta('property', "article:{$key}", $value);
        }

        return "<!DOCTYPE html>\n<html lang=\"en\">\n<head>\n"
            ."    <meta charset=\"UTF-8\">\n"
            .'    <title>'.e($c['title'])."</title>\n"
            .'    <link rel="canonical" href="'.e($c['url'])."\">\n"
            .$tags
            ."</head>\n<body>\n"
            .'    <h1>'.e($c['title'])."</h1>\n"
            .'    <p>'.e($c['description'])."</p>\n"
            .'    <p><a href="'.e($c['url']).'">'.e($c['url'])."</a></p>\n"
            ."</body>\n</html>\n";
    }
}
