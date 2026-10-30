<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Support\Audit\Audit;
use App\Models\Comment;
use App\Models\Post;
use App\Support\Tenancy\CurrentTenant;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CommentController extends Controller
{
    public function index(Post $post): JsonResponse
    {
        $comments = Comment::with(['user:id,name,avatar', 'replies.user:id,name,avatar'])
            ->where('post_id', $post->id)
            ->whereNull('parent_id')
            ->where('status', 'approved')
            ->latest()
            ->paginate(20);

        return response()->json($comments);
    }

    public function store(Request $request, Post $post): JsonResponse
    {
        $data = $request->validate([
            'content'   => 'required|string|max:2000',
            'parent_id' => ['nullable', Rule::exists('comments', 'id')->where('post_id', $post->id)],
        ]);

        $comment = Comment::create([
            'post_id'   => $post->id,
            'user_id'   => $request->user()->id,
            'parent_id' => $data['parent_id'] ?? null,
            'content'   => $data['content'],
            'status'    => 'approved',
        ]);

        return response()->json($comment->load('user:id,name,avatar'), 201);
    }

    public function destroy(Request $request, Comment $comment): JsonResponse
    {
        if ($comment->user_id !== $request->user()->id
            && ! app(CurrentTenant::class)->userHasRole($request->user(), ['admin', 'editor'])) {
            return response()->json(['message' => 'Unauthorized.'], 403);
        }
        Audit::record('comment.deleted', $comment, ['post' => [$comment->post_id, null]]);
        $comment->delete();
        return response()->json(['message' => 'Comment deleted.']);
    }

    public function adminIndex(Request $request): JsonResponse
    {
        $comments = Comment::with(['user:id,name,email', 'post:id,title,slug'])
            ->when($request->status && $request->status !== 'all', fn ($q, $s) => $q->where('status', $s))
            ->when($request->search, fn ($q, $s) => $q->where('content', 'like', "%{$s}%"))
            ->latest()
            ->paginate(min((int) ($request->per_page ?? 20), 100));

        return response()->json($comments);
    }

    public function updateStatus(Request $request, Comment $comment): JsonResponse
    {
        $request->validate(['status' => 'required|in:pending,approved,rejected,spam']);
        $old = $comment->status;
        $comment->update(['status' => $request->status]);
        Audit::record('comment.status_changed', $comment, ['status' => [$old, $request->status]]);
        return response()->json($comment);
    }
}
