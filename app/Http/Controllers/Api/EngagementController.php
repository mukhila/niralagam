<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Bookmark;
use App\Models\Post;
use App\Models\PostView;
use App\Models\Wishlist;
use App\Support\Analytics\Traffic;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class EngagementController extends Controller
{
    /**
     * Counts one view per reader (account, else IP) per post per 24h. Bots and
     * unpublished posts aren't counted. The page sends document.referrer and the
     * URL's utm_source so analytics can show where readers come from.
     */
    public function recordView(Request $request, Post $post): JsonResponse
    {
        $data = $request->validate([
            'referrer'   => 'nullable|string|max:2048',
            'utm_source' => 'nullable|string|max:100',
        ]);

        $userId = auth('sanctum')->id();
        $agent  = $request->userAgent();

        if ($post->isLive() && ! Traffic::isBot($agent)) {
            $exists = PostView::where('post_id', $post->id)
                ->when($userId, fn ($q) => $q->where('user_id', $userId), fn ($q) => $q->where('ip_address', $request->ip()))
                ->where('viewed_at', '>=', now()->subHours(24))
                ->exists();

            if (! $exists) {
                [$source, $host] = Traffic::source(
                    $data['referrer'] ?? null,
                    $data['utm_source'] ?? null,
                    $request->header('X-Tenant-Domain') ?: $request->getHost(),
                );

                PostView::create([
                    'post_id'       => $post->id,
                    'user_id'       => $userId,
                    'ip_address'    => $request->ip(),
                    'user_agent'    => $agent ? substr($agent, 0, 255) : null,
                    'source'        => $source,
                    'referrer_host' => $host,
                    'device'        => Traffic::device($agent),
                ]);
            }
        }

        return response()->json(['views' => PostView::where('post_id', $post->id)->count()]);
    }

    public function toggleWishlist(Request $request, Post $post): JsonResponse
    {
        $user = $request->user();
        $row  = Wishlist::where('user_id', $user->id)->where('post_id', $post->id)->first();

        if ($row) {
            $row->delete();
            return response()->json(['wishlisted' => false, 'count' => Wishlist::where('post_id', $post->id)->count()]);
        }

        Wishlist::create(['user_id' => $user->id, 'post_id' => $post->id]);
        return response()->json(['wishlisted' => true, 'count' => Wishlist::where('post_id', $post->id)->count()]);
    }

    public function toggleBookmark(Request $request, Post $post): JsonResponse
    {
        $user = $request->user();
        $row  = Bookmark::where('user_id', $user->id)->where('post_id', $post->id)->first();

        if ($row) {
            $row->delete();
            return response()->json(['bookmarked' => false, 'count' => Bookmark::where('post_id', $post->id)->count()]);
        }

        Bookmark::create(['user_id' => $user->id, 'post_id' => $post->id]);
        return response()->json(['bookmarked' => true, 'count' => Bookmark::where('post_id', $post->id)->count()]);
    }

    public function postEngagement(Request $request, Post $post): JsonResponse
    {
        $userId = auth('sanctum')->id();

        return response()->json([
            'views'      => $post->views()->count(),
            'wishlists'  => $post->wishlists()->count(),
            'bookmarks'  => $post->bookmarks()->count(),
            'comments'   => $post->comments()->where('status', 'approved')->count(),
            'wishlisted' => $userId ? Wishlist::where('user_id', $userId)->where('post_id', $post->id)->exists() : false,
            'bookmarked' => $userId ? Bookmark::where('user_id', $userId)->where('post_id', $post->id)->exists() : false,
        ]);
    }
}
