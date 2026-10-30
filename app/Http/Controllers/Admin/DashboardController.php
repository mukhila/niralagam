<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Post;
use App\Models\Tag;
use App\Models\User;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $stats = [
            'posts'      => Post::count(),
            'published'  => Post::where('status', 'published')->count(),
            'drafts'     => Post::where('status', 'draft')->count(),
            'categories' => Category::count(),
            'tags'       => Tag::count(),
        ];

        $recentPosts = Post::with('category')
            ->latest()
            ->limit(5)
            ->get();

        return view('admin.dashboard', compact('stats', 'recentPosts'));
    }
}
