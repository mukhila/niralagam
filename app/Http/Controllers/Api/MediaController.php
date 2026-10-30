<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Support\Audit\Audit;
use App\Models\Media;
use App\Models\Post;
use App\Support\Editorial\PostWorkflow;
use App\Support\Media\ImageProcessor;
use App\Support\Tenancy\CurrentTenant;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use RuntimeException;

/**
 * The current brand's media library. Anyone who may write posts in the brand
 * can upload and browse it; uploaders manage their own files and editors/admins
 * manage everything.
 */
class MediaController extends Controller
{
    private const WRITER_ROLES = ['admin', 'editor', 'author'];

    public function __construct(private PostWorkflow $workflow) {}

    public function index(Request $request): JsonResponse
    {
        $this->ensureWriter($request);
        $data = $request->validate([
            'search'   => 'nullable|string|max:100',
            'mine'     => 'nullable|boolean',
            'per_page' => 'nullable|integer|min:1|max:100',
        ]);

        $page = Media::with('user:id,name')
            ->when($data['search'] ?? null, fn ($q, $s) => $q->where(fn ($q) =>
                $q->where('original_name', 'like', "%{$s}%")->orWhere('alt', 'like', "%{$s}%")))
            ->when($request->boolean('mine'), fn ($q) => $q->where('user_id', $request->user()->id))
            ->latest('id')
            ->paginate((int) ($data['per_page'] ?? 30));

        $page->getCollection()->transform(fn (Media $m) => $this->present($m, $request));

        return response()->json($page);
    }

    public function store(Request $request): JsonResponse
    {
        $this->ensureWriter($request);
        // No SVG: it can carry scripts.
        $request->validate(['image' => 'required|file|mimetypes:image/jpeg,image/png,image/webp,image/gif|max:8192|dimensions:max_width=12000,max_height=12000']);
        $request->validate(['alt' => 'nullable|string|max:255']);

        try {
            $media = app(ImageProcessor::class)->store($request->file('image'), app(CurrentTenant::class)->get(), $request->user());
        } catch (RuntimeException $e) {
            return response()->json(['message' => $e->getMessage(), 'errors' => ['image' => [$e->getMessage()]]], 422);
        }

        if ($request->filled('alt')) {
            $media->update(['alt' => $request->input('alt')]);
        }

        Audit::record('media.uploaded', $media);

        return response()->json($this->present($media->load('user:id,name'), $request), 201);
    }

    public function update(Request $request, Media $media): JsonResponse
    {
        $this->ensureCanManage($request, $media);
        $data = $request->validate(['alt' => 'nullable|string|max:255']);

        $media->update(['alt' => $data['alt'] ?? null]);
        Audit::record('media.alt_updated', $media, Audit::diff($media, ['alt']));

        return response()->json($this->present($media->load('user:id,name'), $request));
    }

    /** Where the image is used, so the library can warn before deleting. */
    public function usage(Request $request, Media $media): JsonResponse
    {
        $this->ensureWriter($request);

        return response()->json($this->usedIn($media));
    }

    public function destroy(Request $request, Media $media): JsonResponse
    {
        $this->ensureCanManage($request, $media);

        $used = $this->usedIn($media);
        if (($used['posts'] || $used['brand_logo']) && ! $request->boolean('force')) {
            return response()->json([
                'message' => 'This image is still used. Remove it from those places first, or delete anyway.',
                'usage'   => $used,
            ], 409);
        }

        Audit::record('media.deleted', $media, $request->boolean('force') ? ['deleted_while_in_use' => [null, true]] : []);
        Storage::disk('public')->delete($media->files());
        $media->delete();

        return response()->json(['message' => 'Image deleted.']);
    }

    private function usedIn(Media $media): array
    {
        // Every stored file name starts with the same random id, so one LIKE finds any size.
        $stem  = pathinfo($media->path, PATHINFO_FILENAME);
        $like  = '%'.$stem.'%';
        $posts = Post::query()
            ->where(fn ($q) => $q->where('content', 'like', $like)
                ->orWhere('featured_image', 'like', $like)
                ->orWhere('og_image', 'like', $like))
            ->limit(20)->get(['id', 'title', 'slug', 'status']);

        return [
            'posts'      => $posts,
            'brand_logo' => str_contains((string) app(CurrentTenant::class)->get()->logo, $stem),
        ];
    }

    private function present(Media $m, Request $request): array
    {
        return [
            ...$m->only('id', 'original_name', 'mime', 'size', 'width', 'height', 'alt', 'url', 'urls', 'created_at'),
            'uploaded_by' => $m->user?->name,
            'can_manage'  => $this->canManage($request, $m),
        ];
    }

    private function ensureWriter(Request $request): void
    {
        $tenant = app(CurrentTenant::class);
        if (! $tenant->get()?->allowsPublicPosts() && ! $tenant->userHasRole($request->user(), self::WRITER_ROLES)) {
            abort(403, 'Only people who write for this brand can use its media library.');
        }
    }

    private function canManage(Request $request, Media $media): bool
    {
        return $media->user_id === $request->user()?->id || $this->workflow->isManager($request->user());
    }

    private function ensureCanManage(Request $request, Media $media): void
    {
        if (! $this->canManage($request, $media)) {
            abort(403, 'You can only change images you uploaded.');
        }
    }
}
