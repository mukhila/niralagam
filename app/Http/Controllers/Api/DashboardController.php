<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Bookmark;
use App\Models\Category;
use App\Models\Comment;
use App\Models\Post;
use App\Models\PostView;
use App\Models\Tag;
use App\Models\Tenant;
use App\Models\User;
use App\Models\Wishlist;
use App\Support\Tenancy\CurrentTenant;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class DashboardController extends Controller
{
    /** Super admins see platform-wide user and tenant figures; brand staff see their brand only. */
    private bool $platform = false;

    public function __construct(private CurrentTenant $tenant) {}

    public function index(Request $request): JsonResponse
    {
        $this->platform = $request->user()->isSuperAdmin();

        $range  = $request->query('range', 'all');
        $from   = $request->query('from');
        $to     = $request->query('to');

        [$startDate, $endDate] = $this->resolveDateRange($range, $from, $to);

        $stats = $this->buildStats($startDate, $endDate);
        $recentPosts    = $this->recentPosts();
        $topPosts       = $this->topPosts();
        $recentUsers    = $this->recentUsers();
        $tenants        = $this->recentTenants();
        $popularCats    = $this->popularCategories();
        $popularTags    = $this->popularTags();
        $dailyViews     = $this->dailyViews($startDate, $endDate);
        $moderationAlerts = $this->moderationAlerts();

        return response()->json(compact(
            'stats', 'recentPosts', 'topPosts', 'recentUsers',
            'tenants', 'popularCats', 'popularTags', 'dailyViews',
            'moderationAlerts'
        ) + [
            'scope' => $this->platform ? 'platform' : 'brand',
            'brand' => $this->tenant->get()?->only('id', 'name', 'slug'),
        ]);
    }

    private function resolveDateRange(string $range, ?string $from, ?string $to): array
    {
        $now = Carbon::now();
        return match ($range) {
            'today'   => [$now->copy()->startOfDay(), $now->copy()->endOfDay()],
            '7d'      => [$now->copy()->subDays(7)->startOfDay(), $now->copy()->endOfDay()],
            '30d'     => [$now->copy()->subDays(30)->startOfDay(), $now->copy()->endOfDay()],
            'month'   => [$now->copy()->startOfMonth(), $now->copy()->endOfMonth()],
            'custom'  => [
                $from ? Carbon::parse($from)->startOfDay() : $now->copy()->subDays(30)->startOfDay(),
                $to   ? Carbon::parse($to)->endOfDay()   : $now->copy()->endOfDay(),
            ],
            default   => [null, null],
        };
    }

    private function buildStats(?Carbon $from, ?Carbon $to): array
    {
        $postQ   = fn($status) => Post::where('status', $status);
        $viewQ   = PostView::query();
        $wQ      = Wishlist::query();
        $bQ      = Bookmark::query();
        $cQ      = Comment::query();
        // Brand staff: "users" means the brand's members, not every account on the platform.
        $uQ      = $this->platform ? User::where('role', 'user') : $this->tenant->get()->users();

        if ($from) {
            $viewQ->whereBetween('viewed_at', [$from, $to]);
            $wQ->whereBetween('created_at', [$from, $to]);
            $bQ->whereBetween('created_at', [$from, $to]);
            $cQ->whereBetween('created_at', [$from, $to]);
            $uQ->whereBetween('users.created_at', [$from, $to]);
        }

        $tenantCount = fn (?string $status = null) => $this->platform
            ? Tenant::when($status, fn ($q) => $q->where('status', $status))->count()
            : 0;

        return [
            'tenants_total'    => $tenantCount(),
            'tenants_active'   => $tenantCount('active'),
            'tenants_inactive' => $tenantCount('inactive'),
            'tenants_suspended'=> $tenantCount('suspended'),
            'users'            => $uQ->count(),
            'posts_published'  => $postQ('published')->count(),
            'posts_draft'      => $postQ('draft')->count(),
            'posts_pending'    => $postQ('pending')->count(),
            'posts_scheduled'  => $postQ('scheduled')->count(),
            'posts_rejected'   => $postQ('rejected')->count(),
            'views'            => (clone $viewQ)->count(),
            'wishlists'        => (clone $wQ)->count(),
            'bookmarks'        => (clone $bQ)->count(),
            'comments'         => (clone $cQ)->where('status', 'approved')->count(),
            'pending_comments' => Comment::where('status', 'pending')->count(),
            'categories'       => Category::count(),
            'tags'             => Tag::count(),
        ];
    }

    private function recentPosts(): \Illuminate\Database\Eloquent\Collection
    {
        return Post::with(['category', 'user:id,name'])
            ->withCount(['views', 'wishlists', 'bookmarks', 'comments'])
            ->orderByDesc('created_at')
            ->take(10)
            ->get(['id','title','slug','featured_image','status','published_at','created_at','category_id','user_id']);
    }

    private function topPosts(): \Illuminate\Database\Eloquent\Collection
    {
        return Post::with(['category'])
            ->withCount('views')
            ->where('status', 'published')
            ->orderByDesc('views_count')
            ->take(8)
            ->get(['id','title','slug','featured_image','category_id']);
    }

    private function recentUsers(): \Illuminate\Database\Eloquent\Collection
    {
        return ($this->platform ? User::query() : $this->tenant->get()->users())
            ->orderByDesc('users.created_at')
            ->take(6)
            ->get(['users.id','users.name','users.email','users.role','users.created_at']);
    }

    private function recentTenants(): \Illuminate\Database\Eloquent\Collection
    {
        if (! $this->platform) {
            return new \Illuminate\Database\Eloquent\Collection();
        }

        return Tenant::withCount('users')
            ->withCount('posts')
            ->orderByDesc('created_at')
            ->take(6)
            ->get();
    }

    private function popularCategories(): \Illuminate\Database\Eloquent\Collection
    {
        return Category::withCount('posts')
            ->orderByDesc('posts_count')
            ->take(6)
            ->get(['id','name','icon','slug']);
    }

    private function popularTags(): \Illuminate\Database\Eloquent\Collection
    {
        return Tag::withCount('posts')
            ->orderByDesc('posts_count')
            ->take(8)
            ->get(['id','name','slug']);
    }

    private function dailyViews(?Carbon $from, ?Carbon $to): array
    {
        $start = $from ?? Carbon::now()->subDays(29)->startOfDay();
        $end   = $to   ?? Carbon::now()->endOfDay();

        $rows = PostView::selectRaw('DATE(viewed_at) as date, COUNT(*) as views')
            ->whereBetween('viewed_at', [$start, $end])
            ->groupBy('date')
            ->orderBy('date')
            ->get()
            ->keyBy('date');

        $result = [];
        $cursor = $start->copy()->startOfDay();
        while ($cursor->lte($end)) {
            $key = $cursor->toDateString();
            $result[] = [
                'date'  => $key,
                'views' => $rows[$key]->views ?? 0,
            ];
            $cursor->addDay();
        }

        return $result;
    }

    private function moderationAlerts(): array
    {
        return [
            'pending_posts'    => Post::where('status', 'pending')->count(),
            'pending_comments' => Comment::where('status', 'pending')->count(),
            'reported_posts'   => 0,
        ];
    }
}
