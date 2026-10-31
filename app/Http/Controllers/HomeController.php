<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Post;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Http\Response;
use Illuminate\View\View;

class HomeController extends Controller
{
    public function index(): View
    {
        $heroPost    = Post::with('category', 'user')->live()->whereNotNull('featured_image')->latest('published_at')->first();
        $trendingPost  = Post::with('category', 'user')->live()->latest('published_at')->first();
        $trendingSmall = Post::with('category', 'user')->live()->latest('published_at')->skip(1)->limit(4)->get();
        $featuredTwo   = Post::with('category', 'user')->live()->whereNotNull('featured_image')->latest('published_at')->skip(1)->limit(2)->get();

        $latestQuery = Post::with('category', 'user')->live()->latest('published_at');
        if ($categorySlug = request('category')) {
            $latestQuery->whereHas('category', fn ($q) => $q->where('slug', $categorySlug));
        }
        if ($tagSlug = request('tag')) {
            $latestQuery->whereHas('tags', fn ($q) => $q->where('slug', $tagSlug));
        }

        $latestPosts   = $latestQuery->paginate(5)->withQueryString();
        $sidebarPosts  = Post::with('category')->live()->latest('published_at')->limit(4)->get();
        $categories    = Category::withCount('posts')->orderBy('name')->get();
        $tags          = Tag::withCount('posts')->orderByDesc('posts_count')->limit(10)->get();

        return view('home', compact(
            'heroPost', 'trendingPost', 'trendingSmall', 'featuredTwo',
            'latestPosts', 'sidebarPosts', 'categories', 'tags'
        ));
    }

    public function show(string $slug): View
    {
        $post = Post::with('category', 'user', 'tags')
            ->live()
            ->where('slug', $slug)
            ->firstOrFail();

        $related = Post::with('category', 'user')
            ->live()
            ->where('id', '!=', $post->id)
            ->when($post->category_id, fn ($q) => $q->where('category_id', $post->category_id))
            ->latest('published_at')
            ->limit(3)
            ->get();

        $recentPosts = Post::with('category')->live()->latest('published_at')->limit(4)->get();
        $tags        = Tag::withCount('posts')->orderByDesc('posts_count')->limit(10)->get();

        return view('post', compact('post', 'related', 'recentPosts', 'tags'));
    }

    public function author(string $handle): View
    {
        $userId = User::idForHandle($handle);
        $author = User::findOrFail($userId);

        $posts = Post::with('category')
            ->live()
            ->where('user_id', $author->id)
            ->latest('published_at')
            ->paginate(6);

        return view('author', compact('author', 'posts'));
    }

    public function sitemap(): Response
    {
        $base  = rtrim(config('app.url'), '/');
        $posts = Post::live()->latest('published_at')->get(['slug', 'updated_at', 'published_at', 'user_id']);

        $urls = [];

        // Home
        $urls[] = ['loc' => $base . '/', 'lastmod' => $posts->first()?->updated_at, 'priority' => '1.0', 'changefreq' => 'daily'];

        // Category filter pages
        foreach (Category::orderBy('name')->get(['slug', 'updated_at']) as $cat) {
            $urls[] = ['loc' => $base . '/?category=' . rawurlencode($cat->slug), 'lastmod' => null, 'priority' => '0.6', 'changefreq' => 'weekly'];
        }

        // Author pages (only authors with at least one live post)
        $authorIds = $posts->pluck('user_id')->unique();
        foreach (User::whereIn('id', $authorIds)->get(['id', 'username', 'updated_at']) as $author) {
            $urls[] = ['loc' => $base . '/author/' . rawurlencode($author->handle()), 'lastmod' => null, 'priority' => '0.6', 'changefreq' => 'weekly'];
        }

        // Post pages
        foreach ($posts as $post) {
            $urls[] = ['loc' => $base . '/blog/' . rawurlencode($post->slug), 'lastmod' => $post->updated_at, 'priority' => '0.8', 'changefreq' => 'monthly'];
        }

        $xml = '<?xml version="1.0" encoding="UTF-8"?>' . "\n"
            . '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";

        foreach ($urls as $u) {
            $xml .= '  <url>'
                . '<loc>' . e($u['loc']) . '</loc>'
                . ($u['lastmod'] ? '<lastmod>' . $u['lastmod']->toAtomString() . '</lastmod>' : '')
                . '<changefreq>' . $u['changefreq'] . '</changefreq>'
                . '<priority>' . $u['priority'] . '</priority>'
                . '</url>' . "\n";
        }

        $xml .= '</urlset>' . "\n";

        return response($xml, 200, [
            'Content-Type'  => 'application/xml; charset=UTF-8',
            'Cache-Control' => 'public, max-age=3600',
        ]);
    }

    public function robots(): Response
    {
        $sitemapUrl = rtrim(config('app.url'), '/') . '/sitemap.xml';

        $content = implode("\n", [
            'User-agent: *',
            'Allow: /',
            'Disallow: /admin',
            'Disallow: /super-admin',
            'Disallow: /login',
            'Disallow: /logout',
            '',
            "Sitemap: {$sitemapUrl}",
            '',
        ]);

        return response($content, 200, [
            'Content-Type' => 'text/plain; charset=UTF-8',
        ]);
    }
}
