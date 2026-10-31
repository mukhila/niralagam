<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Tag;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TagController extends Controller
{
    public function index(): View
    {
        $tags = Tag::withCount('posts')->orderBy('name')->get();

        return view('admin.tags.index', compact('tags'));
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate(['name' => 'required|string|max:100']);

        Tag::create(['name' => $request->name, 'slug' => Tag::uniqueSlug($request->name)]);

        return redirect()->route('admin.tags.index')
            ->with('success', 'Tag created.');
    }

    public function update(Request $request, Tag $tag): RedirectResponse
    {
        $request->validate(['name' => 'required|string|max:100']);

        $data = ['name' => $request->name];
        if ($request->name !== $tag->name) {
            $data['slug'] = Tag::uniqueSlug($request->name, $tag->id);
        }

        $tag->update($data);

        return redirect()->route('admin.tags.index')
            ->with('success', 'Tag updated.');
    }

    public function destroy(Tag $tag): RedirectResponse
    {
        $tag->delete();

        return redirect()->route('admin.tags.index')
            ->with('success', 'Tag deleted.');
    }
}
