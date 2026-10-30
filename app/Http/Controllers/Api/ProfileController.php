<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Bookmark;
use App\Models\Post;
use App\Models\Wishlist;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class ProfileController extends Controller
{
    public function show(Request $request): JsonResponse
    {
        $user = $request->user()->loadCount(['posts', 'wishlists', 'bookmarks', 'comments']);
        return response()->json($user);
    }

    public function update(Request $request): JsonResponse
    {
        $user = $request->user();
        $data = $request->validate([
            'name'     => 'required|string|max:100',
            'username' => 'nullable|string|max:50|unique:users,username,' . $user->id,
            'bio'      => 'nullable|string|max:500',
            'website'  => 'nullable|url|max:255',
        ]);
        $user->update($data);
        return response()->json($user->fresh());
    }

    public function changePassword(Request $request): JsonResponse
    {
        $data = $request->validate([
            'current_password' => 'required',
            'password'         => 'required|min:8|confirmed',
        ]);

        if (! Hash::check($data['current_password'], $request->user()->password)) {
            return response()->json(['message' => 'Current password is incorrect.'], 422);
        }

        $request->user()->update(['password' => Hash::make($data['password'])]);
        return response()->json(['message' => 'Password updated successfully.']);
    }

    public function wishlist(Request $request): JsonResponse
    {
        $items = Wishlist::with(['post.category', 'post.user:id,name'])
            ->whereHas('post')
            ->where('user_id', $request->user()->id)
            ->latest()
            ->paginate(12);
        return response()->json($items);
    }

    public function bookmarks(Request $request): JsonResponse
    {
        $items = Bookmark::with(['post.category', 'post.user:id,name'])
            ->whereHas('post')
            ->where('user_id', $request->user()->id)
            ->latest()
            ->paginate(12);
        return response()->json($items);
    }

    public function myPosts(Request $request): JsonResponse
    {
        $posts = Post::with(['category', 'tags'])
            ->withCount(['views', 'wishlists', 'bookmarks', 'comments'])
            ->where('user_id', $request->user()->id)
            ->orderByDesc('created_at')
            ->paginate(10);
        return response()->json($posts);
    }
}
