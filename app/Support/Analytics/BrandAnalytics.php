<?php

namespace App\Support\Analytics;

use App\Models\Comment;
use App\Models\Follow;
use App\Models\NewsletterSubscriber;
use App\Models\Post;
use App\Models\PostView;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

/**
 * Analytics for the current brand (tenant scopes apply), optionally limited to
 * one author's posts. Every KPI comes with the previous period of the same
 * length so the UI can show the change.
 */
class BrandAnalytics
{
    public const RANGES = ['7d' => 7, '30d' => 30, '90d' => 90];

    private Carbon $from;
    private Carbon $to;
    private Carbon $prevFrom;

    public function __construct(private ?int $authorId = null, string $range = '30d')
    {
        $days           = self::RANGES[$range] ?? 30;
        $this->to       = now()->endOfDay();
        $this->from     = now()->subDays($days - 1)->startOfDay();
        $this->prevFrom = $this->from->copy()->subDays($days);
    }

    public function report(): array
    {
        $scopeOnly = $this->authorId === null;

        return [
            'range'   => ['from' => $this->from->toDateString(), 'to' => $this->to->toDateString()],
            'kpis'    => array_filter([
                'views'           => $this->compare(fn ($a, $b) => $this->views()->whereBetween('viewed_at', [$a, $b])->count()),
                'visitors'        => $this->compare(fn ($a, $b) => (int) $this->views()->whereBetween('viewed_at', [$a, $b])
                    ->selectRaw('COUNT(DISTINCT COALESCE(CAST(user_id AS CHAR), ip_address)) as n')->value('n')),
                'posts_published' => $this->compare(fn ($a, $b) => $this->posts()->live()->whereBetween('published_at', [$a, $b])->count()),
                'comments'        => $this->compare(fn ($a, $b) => Comment::where('status', 'approved')
                    ->whereIn('post_id', $this->posts()->select('id'))->whereBetween('created_at', [$a, $b])->count()),
                'new_followers'   => $this->compare(fn ($a, $b) => Follow::query()
                    ->when($this->authorId, fn ($q) => $q->where('author_id', $this->authorId))
                    ->whereBetween('created_at', [$a, $b])->count()),
                // Newsletter is brand-wide, so authors don't see it.
                'new_subscribers' => $scopeOnly ? $this->compare(fn ($a, $b) => NewsletterSubscriber::where('status', 'subscribed')
                    ->whereBetween('confirmed_at', [$a, $b])->count()) : null,
            ]),
            'daily'     => $this->daily(),
            'top_posts' => $this->topPosts(),
            'sources'   => $this->breakdown('source', Traffic::SOURCES),
            'devices'   => $this->breakdown('device', ['mobile', 'desktop', 'tablet']),
            'referrers' => $this->referrers(),
            'authors'   => $scopeOnly ? $this->topAuthors() : [],
            'subscribers_daily' => $scopeOnly ? $this->subscribersDaily() : [],
        ];
    }

    private function views(): Builder
    {
        return PostView::query()->when($this->authorId, fn ($q) => $q->whereIn('post_id', $this->posts()->select('id')));
    }

    private function posts(): Builder
    {
        return Post::query()->when($this->authorId, fn ($q) => $q->where('user_id', $this->authorId));
    }

    /** {value, previous} for the current and the previous period. */
    private function compare(callable $count): array
    {
        return [
            'value'    => $count($this->from, $this->to),
            'previous' => $count($this->prevFrom, $this->from->copy()->subSecond()),
        ];
    }

    /** One row per day (zero-filled) with views and unique visitors. */
    private function daily(): array
    {
        $rows = $this->views()->whereBetween('viewed_at', [$this->from, $this->to])
            ->selectRaw('DATE(viewed_at) as day, COUNT(*) as views, COUNT(DISTINCT COALESCE(CAST(user_id AS CHAR), ip_address)) as visitors')
            ->groupBy('day')->get()->keyBy(fn ($r) => (string) $r->day);

        return $this->eachDay(fn ($day) => [
            'date'     => $day,
            'views'    => (int) ($rows[$day]->views ?? 0),
            'visitors' => (int) ($rows[$day]->visitors ?? 0),
        ]);
    }

    private function subscribersDaily(): array
    {
        $rows = NewsletterSubscriber::where('status', 'subscribed')->whereBetween('confirmed_at', [$this->from, $this->to])
            ->selectRaw('DATE(confirmed_at) as day, COUNT(*) as n')->groupBy('day')->pluck('n', 'day');

        return $this->eachDay(fn ($day) => ['date' => $day, 'subscribers' => (int) ($rows[$day] ?? 0)]);
    }

    private function eachDay(callable $row): array
    {
        $out = [];
        for ($d = $this->from->copy(); $d->lte($this->to); $d->addDay()) {
            $out[] = $row($d->toDateString());
        }

        return $out;
    }

    private function topPosts(): array
    {
        $counts = $this->views()->whereBetween('viewed_at', [$this->from, $this->to])
            ->selectRaw('post_id, COUNT(*) as views')->groupBy('post_id')->orderByDesc('views')->limit(10)
            ->pluck('views', 'post_id');

        $posts = Post::with('user:id,name,username')->whereIn('id', $counts->keys())->get(['id', 'title', 'slug', 'user_id', 'published_at'])->keyBy('id');

        return $counts->map(fn ($views, $id) => isset($posts[$id]) ? [
            'id'     => $id,
            'title'  => $posts[$id]->title,
            'slug'   => $posts[$id]->slug,
            'author' => $posts[$id]->user?->name,
            'views'  => (int) $views,
        ] : null)->filter()->values()->all();
    }

    /** Views per value of $column, every known value present (zero when unused). */
    private function breakdown(string $column, array $values): array
    {
        $rows = $this->views()->whereBetween('viewed_at', [$this->from, $this->to])
            ->selectRaw("{$column} as k, COUNT(*) as n")->groupBy('k')->pluck('n', 'k');

        return collect($values)->map(fn ($v) => ['key' => $v, 'views' => (int) ($rows[$v] ?? 0)])
            ->sortByDesc('views')->values()->all();
    }

    private function referrers(): array
    {
        return $this->views()->whereBetween('viewed_at', [$this->from, $this->to])->whereNotNull('referrer_host')
            ->selectRaw('referrer_host as host, source, COUNT(*) as views')->groupBy('referrer_host', 'source')
            ->orderByDesc('views')->limit(8)->get()
            ->map(fn ($r) => ['host' => $r->host, 'source' => $r->source, 'views' => (int) $r->views])->all();
    }

    private function topAuthors(): array
    {
        return PostView::query()->whereBetween('post_views.viewed_at', [$this->from, $this->to])
            ->join('posts', 'posts.id', '=', 'post_views.post_id')
            ->join('users', 'users.id', '=', 'posts.user_id')
            ->groupBy('users.id', 'users.name', 'users.username')
            ->selectRaw('users.id, users.name, users.username, COUNT(*) as views, COUNT(DISTINCT posts.id) as posts')
            ->orderByDesc('views')->limit(8)->get()
            ->map(fn ($r) => ['id' => $r->id, 'name' => $r->name, 'handle' => $r->username ?: (string) $r->id,
                'views' => (int) $r->views, 'posts' => (int) $r->posts])->all();
    }
}
