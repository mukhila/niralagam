<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Follow;
use App\Models\Post;
use App\Models\Tenant;
use App\Models\User;
use App\Support\Tenancy\CurrentTenant;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Public author pages and following (authors and the brand itself).
 * An author only has a page in a brand once they have a live post there, so
 * this never exposes accounts that haven't published anything.
 */
class AuthorController extends Controller
{
    private function tenant(): Tenant
    {
        return app(CurrentTenant::class)->get();
    }

    public function show(Request $request, string $handle): JsonResponse
    {
        $author = $this->findAuthor($handle);
        $viewer = $request->user('sanctum');

        return response()->json([
            ...$author->only('id', 'name', 'avatar', 'bio', 'website'),
            'handle'          => $author->handle(),
            'posts_count'     => Post::live()->where('user_id', $author->id)->count(),
            'followers_count' => Follow::where('author_id', $author->id)->count(),
            'is_following'    => $viewer ? $this->follows($viewer, $author->id) : false,
            'is_self'         => $viewer?->id === $author->id,
        ]);
    }

    public function posts(Request $request, string $handle): JsonResponse
    {
        $author = $this->findAuthor($handle);

        $posts = Post::with(['category', 'tags', 'user:id,name,username'])
            ->withCount(['views', 'wishlists', 'bookmarks', 'comments'])
            ->where('user_id', $author->id)
            ->live()
            ->orderByDesc('published_at')
            ->orderByDesc('id')
            ->paginate(min((int) ($request->per_page ?? 10), 100));

        return response()->json($posts);
    }

    public function toggleFollowAuthor(Request $request, string $handle): JsonResponse
    {
        $author = $this->findAuthor($handle);
        $user   = $request->user();

        if ($user->id === $author->id) {
            abort(422, "You can't follow yourself.");
        }

        $following = $this->toggle($user, $author->id);

        return response()->json([
            'following'       => $following,
            'followers_count' => Follow::where('author_id', $author->id)->count(),
        ]);
    }

    public function toggleFollowBrand(Request $request): JsonResponse
    {
        $following = $this->toggle($request->user(), null);

        return response()->json([
            'following'       => $following,
            'followers_count' => Follow::whereNull('author_id')->count(),
        ]);
    }

    /** What the signed-in reader follows in this brand (for button states). */
    public function myFollows(Request $request): JsonResponse
    {
        $follows = Follow::with('author:id,name,username,avatar')->where('user_id', $request->user()->id)->latest()->get();

        return response()->json([
            'brand'   => $follows->contains(fn ($f) => $f->author_id === null),
            'authors' => $follows->whereNotNull('author_id')->map(fn ($f) => [
                ...$f->author->only('id', 'name', 'avatar'),
                'handle'      => $f->author->handle(),
                'followed_at' => $f->created_at,
            ])->values(),
        ]);
    }

    private function findAuthor(string $handle): User
    {
        $id = User::idForHandle($handle);

        if (! $id || ! Post::live()->where('user_id', $id)->exists()) {
            abort(404, 'Author not found.');
        }

        return User::findOrFail($id);
    }

    private function follows(User $user, ?int $authorId): bool
    {
        return Follow::where('user_id', $user->id)
            ->when($authorId, fn ($q) => $q->where('author_id', $authorId), fn ($q) => $q->whereNull('author_id'))
            ->exists();
    }

    /** Follow if not following, else unfollow. Returns the new state. */
    private function toggle(User $user, ?int $authorId): bool
    {
        $existing = Follow::where('user_id', $user->id)
            ->when($authorId, fn ($q) => $q->where('author_id', $authorId), fn ($q) => $q->whereNull('author_id'));

        if ($existing->exists()) {
            $existing->delete();

            return false;
        }

        Follow::create(['user_id' => $user->id, 'author_id' => $authorId]);

        return true;
    }
}
