<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Support\Audit\Audit;
use App\Models\Post;
use App\Models\User;
use App\Support\Editorial\PostWorkflow;
use App\Support\Tenancy\CurrentTenant;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Validation\Rule;

class PostController extends Controller
{
    /** Tenant roles that may create posts when the tenant doesn't allow public posting. */
    private const WRITER_ROLES = ['admin', 'editor', 'author'];

    public function __construct(private PostWorkflow $workflow) {}

    // Resolved per call: controller instances can outlive a request (tests, Octane).
    private function tenant(): CurrentTenant
    {
        return app(CurrentTenant::class);
    }

    public function index(Request $request): JsonResponse
    {
        $query = Post::with(['category', 'tags', 'user:id,name,username'])
            ->withCount(['views', 'wishlists', 'bookmarks', 'comments'])
            // "published" means live on the site, which includes scheduled posts whose time has come.
            ->when($request->status === 'published', fn ($q) => $q->live())
            ->when($request->status && $request->status !== 'published', fn ($q) => $q->where('status', $request->status))
            ->when($request->category, fn ($q, $c) => $q->whereHas('category', fn ($q) => $q->where('slug', $c)))
            ->when($request->tag, fn ($q, $t) => $q->whereHas('tags', fn ($q) => $q->where('slug', $t)))
            // ?author= takes an author page handle: a username, or the user id when there's none.
            ->when($request->author, fn ($q, $a) => $q->where('user_id', User::idForHandle($a)))
            ->when($request->search, fn ($q, $s) => $q->where('title', 'like', "%{$s}%"))
            ->when($request->language, fn ($q, $l) => $q->where('language', $l))
            ->when($request->status === 'pending', fn ($q) => $q->orderBy('submitted_at'), fn ($q) => $q->orderByDesc('published_at'))
            ->orderByDesc('id');

        // Public route: resolve the bearer token optionally. Unpublished posts are
        // only visible to their author, or to the brand's editors and admins.
        $user = $request->user('sanctum');
        if (! $this->workflow->isManager($user)) {
            $query->where(fn ($q) => $q->live()->when($user, fn ($q) => $q->orWhere('user_id', $user->id)));
        }

        return response()->json($query->paginate(min((int) ($request->per_page ?? 10), 100)));
    }

    public function store(Request $request): JsonResponse
    {
        $user = $request->user();

        if (! $this->tenant()->get()?->allowsPublicPosts()
            && ! $this->tenant()->userHasRole($user, self::WRITER_ROLES)) {
            abort(403, 'You are not an author in this tenant.');
        }

        $data = $request->validate($this->rules($request, creating: true));

        $post = new Post(collect($data)->except(['tags', 'status', 'publish_at', 'translation_of'])->all());
        $post->slug     = Post::uniqueSlug($data['title']);
        $post->user_id  = $user->id;
        $post->language = $data['language'] ?? $this->tenant()->get()->languages()['default'];
        if (! empty($data['translation_of'])) {
            $post->translation_group = $this->joinGroup(Post::findOrFail($data['translation_of']), $post->language);
        }
        $this->workflow->applyStatus($post, $data['status'] ?? 'draft', $this->publishAt($data));
        $post->save();
        Audit::record('post.created', $post, ['status' => [null, $post->status]]);

        if (! empty($data['tags'])) {
            $post->tags()->sync($data['tags']);
        }

        if ($post->status === 'pending') {
            $this->workflow->notifySubmitted($post, $user);
        }

        return response()->json($post->load(['category', 'tags', 'user:id,name,username']), 201);
    }

    public function show(Request $request, string $slug): JsonResponse
    {
        $post = Post::with(['category', 'tags', 'user:id,name,username', 'reviewer:id,name'])
            ->withCount(['views', 'wishlists', 'bookmarks', 'comments'])
            // The admin editor loads posts by numeric id; the public site by slug.
            ->where(fn ($q) => $q->where('slug', $slug)
                ->when(ctype_digit($slug), fn ($q) => $q->orWhere('id', (int) $slug)))
            ->orderByRaw('slug = ? desc', [$slug])
            ->firstOrFail();

        $user = $request->user('sanctum');
        if (! $this->workflow->canView($user, $post)) {
            abort(404);
        }

        // Other-language versions: readers see live ones, staff see all.
        $translations = $post->translations()
            ->when(! $this->workflow->isManager($user), fn ($q) => $q->where(fn ($q) => $q->live()
                ->when($user, fn ($q) => $q->orWhere('user_id', $user->id))))
            ->get(['id', 'slug', 'title', 'language', 'status']);

        return response()->json([
            ...$post->toArray(),
            'translations' => $translations,
            'can_edit'     => $this->workflow->canEdit($user, $post),
            'can_review'   => $this->workflow->isManager($user),
        ]);
    }

    public function update(Request $request, Post $post): JsonResponse
    {
        $user = $request->user();
        $this->authorizeEdit($user, $post);

        $data = $request->validate($this->rules($request, creating: false));

        $post->fill(collect($data)->except(['tags', 'status', 'publish_at', 'translation_of'])->all());
        if ($post->isDirty('language') && $post->translation_group
            && Post::where('translation_group', $post->translation_group)->whereKeyNot($post->id)->where('language', $post->language)->exists()) {
            abort(422, 'Another translation of this post already uses that language.');
        }
        if (isset($data['title'])) {
            $post->slug = Post::uniqueSlug($data['title'], $post->id);
        }
        if ($post->isDirty(['title', 'excerpt', 'content'])) {
            $post->snapshot($user);
        }

        $submitted = false;
        if (isset($data['status']) && ($data['status'] !== $post->status || $data['status'] === 'scheduled')) {
            $submitted = $data['status'] === 'pending';
            $this->workflow->applyStatus($post, $data['status'], $this->publishAt($data));
        }
        $post->save();
        Audit::record('post.updated', $post, Audit::diff($post, ['title', 'status', 'language', 'category_id', 'featured_image', 'content', 'published_at']));

        if (array_key_exists('tags', $data)) {
            $post->tags()->sync($data['tags'] ?? []);
        }

        if ($submitted) {
            $this->workflow->notifySubmitted($post, $user);
        }

        return response()->json($post->load(['category', 'tags', 'user:id,name,username']));
    }

    public function destroy(Request $request, Post $post): JsonResponse
    {
        $this->authorizeEdit($request->user(), $post);

        Audit::record('post.deleted', $post, ['status' => [$post->status, null]]);
        $post->delete();
        return response()->json(['message' => 'Post deleted.']);
    }

    private function rules(Request $request, bool $creating): array
    {
        $required = $creating ? 'required' : 'sometimes|required';
        // Categories and tags must belong to the current tenant.
        $inTenant = fn (string $table) => Rule::exists($table, 'id')->where('tenant_id', $this->tenant()->id());

        return [
            'title'          => "$required|string|max:255",
            'content'        => "$required|string",
            'excerpt'        => 'nullable|string',
            'category_id'    => ['nullable', $inTenant('categories')],
            'featured_image' => 'nullable|url:http,https|max:2048',
            // SEO overrides (optional; the site falls back to title / excerpt / featured image)
            'meta_title'       => 'nullable|string|max:70',
            'meta_description' => 'nullable|string|max:170',
            'og_image'         => 'nullable|url:http,https|max:2048',
            // Authors may only save drafts or submit for review.
            'status'         => [Rule::in($this->workflow->allowedStatuses($request->user()))],
            'publish_at'     => 'required_if:status,scheduled|nullable|date|after:now',
            'tags'           => 'nullable|array',
            'tags.*'         => [$inTenant('tags')],
            // One of the brand's enabled languages; translation_of links a new post to an existing one.
            'language'       => ['sometimes', Rule::in($this->tenant()->get()->languages()['enabled'])],
            'translation_of' => $creating ? ['nullable', 'integer', Rule::exists('posts', 'id')->where('tenant_id', $this->tenant()->id())] : ['prohibited'],
        ];
    }

    /** The translation group a new post in $language joins; refuses a second post in the same language. */
    private function joinGroup(Post $source, string $language): int
    {
        $group = $source->translation_group ?? $source->id;

        if (Post::where(fn ($q) => $q->where('translation_group', $group)->orWhere('id', $group))->where('language', $language)->exists()) {
            abort(422, 'This post already has a version in that language.');
        }

        if (! $source->translation_group) {
            $source->forceFill(['translation_group' => $group])->saveQuietly();
        }

        return $group;
    }

    private function publishAt(array $data): ?Carbon
    {
        return isset($data['publish_at']) ? Carbon::parse($data['publish_at']) : null;
    }

    private function authorizeEdit($user, Post $post): void
    {
        if ($this->workflow->canEdit($user, $post)) {
            return;
        }

        abort(403, $user?->id === $post->user_id
            ? 'This post is '.$post->status.'. Only an editor can change it now.'
            : 'You can only manage your own posts.');
    }
}
