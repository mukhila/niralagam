<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Post;
use App\Models\Tag;
use Illuminate\View\View;

class HomeController extends Controller
{
    public function index(): View
    {
        $heroPost    = Post::with('category', 'user')->live()->whereNotNull('featured_image')->latest('published_at')->first();
        $trendingPost  = Post::with('category', 'user')->live()->latest('published_at')->first();
        $trendingSmall = Post::with('category', 'user')->live()->latest('published_at')->skip(1)->limit(4)->get();
        $featuredTwo   = Post::with('category', 'user')->live()->whereNotNull('featured_image')->latest('published_at')->skip(1)->limit(2)->get();
        $latestPosts   = Post::with('category', 'user')->live()->latest('published_at')->paginate(5);
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
}
