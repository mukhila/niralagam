<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Support\Audit\Audit;
use App\Models\Tag;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class TagController extends Controller
{
    public function index(): JsonResponse
    {
        return response()->json(Tag::withCount('posts')->get());
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate(['name' => 'required|string|max:100']);
        $data['slug'] = Tag::uniqueSlug($data['name']);

        $tag = Tag::create($data);
        Audit::record('tag.created', $tag);

        return response()->json($tag, 201);
    }

    public function show(Tag $tag): JsonResponse
    {
        return response()->json($tag->loadCount('posts'));
    }

    public function update(Request $request, Tag $tag): JsonResponse
    {
        $data = $request->validate(['name' => 'required|string|max:100']);
        $data['slug'] = Tag::uniqueSlug($data['name'], $tag->id);
        $tag->update($data);
        Audit::record('tag.updated', $tag, Audit::diff($tag, ['name', 'slug']));

        return response()->json($tag);
    }

    public function destroy(Tag $tag): JsonResponse
    {
        Audit::record('tag.deleted', $tag);
        $tag->delete();

        return response()->json(['message' => 'Tag deleted.']);
    }
}
