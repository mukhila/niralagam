<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Post;
use App\Models\Tag;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class PostController extends Controller
{
    public function index(): View
    {
        $posts = Post::with('category', 'user')
            ->latest()
            ->paginate(20);

        return view('admin.posts.index', compact('posts'));
    }

    public function create(): View
    {
        $categories = Category::orderBy('name')->get();
        $tags       = Tag::orderBy('name')->get();

        return view('admin.posts.form', compact('categories', 'tags'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'title'          => 'required|string|max:255',
            'excerpt'        => 'nullable|string|max:500',
            'content'        => 'nullable|string',
            'featured_image' => 'nullable|string|max:2048',
            'category_id'    => 'nullable|exists:categories,id',
            'status'         => 'required|in:draft,pending,published',
            'tags'           => 'nullable|array',
            'tags.*'         => 'exists:tags,id',
        ]);

        $post = Post::create([
            ...$data,
            'slug'         => Post::uniqueSlug($data['title']),
            'user_id'      => auth()->id(),
            'published_at' => $data['status'] === 'published' ? now() : null,
        ]);

        if (! empty($data['tags'])) {
            $post->tags()->sync($data['tags']);
        }

        return redirect()->route('admin.posts.index')
            ->with('success', 'Post created successfully.');
    }

    public function edit(Post $post): View
    {
        $categories = Category::orderBy('name')->get();
        $tags       = Tag::orderBy('name')->get();

        return view('admin.posts.form', compact('post', 'categories', 'tags'));
    }

    public function update(Request $request, Post $post): RedirectResponse
    {
        $data = $request->validate([
            'title'          => 'required|string|max:255',
            'excerpt'        => 'nullable|string|max:500',
            'content'        => 'nullable|string',
            'featured_image' => 'nullable|string|max:2048',
            'category_id'    => 'nullable|exists:categories,id',
            'status'         => 'required|in:draft,pending,published',
            'tags'           => 'nullable|array',
            'tags.*'         => 'exists:tags,id',
        ]);

        if ($data['status'] === 'published' && $post->status !== 'published') {
            $data['published_at'] = now();
        }

        if ($data['title'] !== $post->title) {
            $data['slug'] = Post::uniqueSlug($data['title'], $post->id);
        }

        $post->update($data);
        $post->tags()->sync($data['tags'] ?? []);

        return redirect()->route('admin.posts.index')
            ->with('success', 'Post updated successfully.');
    }

    public function destroy(Post $post): RedirectResponse
    {
        $post->delete();

        return redirect()->route('admin.posts.index')
            ->with('success', 'Post deleted.');
    }

    public function uploadImage(Request $request): JsonResponse
    {
        $request->validate([
            'image' => 'required|file|image|mimetypes:image/jpeg,image/png,image/webp,image/gif|max:8192',
        ]);

        $path = $request->file('image')->store('admin-uploads', 'public');

        return response()->json([
            'url' => Storage::disk('public')->url($path),
        ]);
    }
}
