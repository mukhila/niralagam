<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Support\Audit\Audit;
use App\Models\Post;
use App\Models\PostRevision;
use App\Support\Editorial\PostWorkflow;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

/** Review actions (submit, approve, reject) and revision history for a post. */
class PostReviewController extends Controller
{
    public function __construct(private PostWorkflow $workflow) {}

    /** Author (or editor) sends a draft or rejected post for review. */
    public function submit(Request $request, Post $post): JsonResponse
    {
        $user = $request->user();
        if (! $this->workflow->canEdit($user, $post)) {
            abort(403, 'You can only submit your own posts.');
        }
        if (! in_array($post->status, ['draft', 'rejected'], true)) {
            abort(422, "Only drafts and rejected posts can be submitted (this one is {$post->status}).");
        }

        $this->workflow->applyStatus($post, 'pending');
        $post->save();
        $this->workflow->notifySubmitted($post, $user);
        Audit::record('post.submitted', $post);

        return response()->json($this->fresh($post));
    }

    /** Editor publishes now, or schedules when publish_at is given. */
    public function approve(Request $request, Post $post): JsonResponse
    {
        $this->ensureManager($request);
        $data = $request->validate([
            'publish_at' => 'nullable|date|after:now',
            'note'       => 'nullable|string|max:1000',
        ]);

        if ($post->isLive() && empty($data['publish_at'])) {
            abort(422, 'This post is already published.');
        }

        $publishAt = isset($data['publish_at']) ? Carbon::parse($data['publish_at']) : null;
        $this->workflow->review($post, $request->user(), $publishAt ? 'scheduled' : 'published', $data['note'] ?? null, $publishAt);
        Audit::record($publishAt ? 'post.scheduled' : 'post.published', $post, $publishAt ? ['publish_at' => [null, $publishAt->toAtomString()]] : []);

        return response()->json($this->fresh($post));
    }

    /** Editor sends the post back to its author with a note. */
    public function reject(Request $request, Post $post): JsonResponse
    {
        $this->ensureManager($request);
        $data = $request->validate(['note' => 'required|string|max:1000']);

        if ($post->status === 'rejected') {
            abort(422, 'This post is already rejected.');
        }

        $this->workflow->review($post, $request->user(), 'rejected', $data['note']);
        Audit::record('post.rejected', $post, ['note' => [null, $data['note']]]);

        return response()->json($this->fresh($post));
    }

    public function revisions(Request $request, Post $post): JsonResponse
    {
        if (! $this->workflow->canEdit($request->user(), $post) && ! $this->workflow->isManager($request->user())) {
            abort(403);
        }

        return response()->json(
            $post->revisions()->with('user:id,name')->get(['id', 'post_id', 'user_id', 'title', 'excerpt', 'content', 'created_at'])
        );
    }

    /** Puts an old version back; the current version becomes a revision first. */
    public function restore(Request $request, Post $post, PostRevision $revision): JsonResponse
    {
        if (! $this->workflow->canEdit($request->user(), $post)) {
            abort(403, 'You cannot edit this post.');
        }
        if ($revision->post_id !== $post->id) {
            abort(404);
        }

        $post->fill($revision->only('title', 'excerpt', 'content'));
        $post->slug = Post::uniqueSlug($revision->title, $post->id);
        if ($post->isDirty(['title', 'excerpt', 'content'])) {
            $post->snapshot($request->user());
        }
        $post->save();
        Audit::record('post.revision_restored', $post, ['revision' => [null, $revision->id]]);

        return response()->json($this->fresh($post));
    }

    private function ensureManager(Request $request): void
    {
        if (! $this->workflow->isManager($request->user())) {
            abort(403, 'Only editors and admins can review posts.');
        }
    }

    private function fresh(Post $post): Post
    {
        return $post->fresh(['category', 'tags', 'user:id,name', 'reviewer:id,name']);
    }
}
