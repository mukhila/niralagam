<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Support\Analytics\BrandAnalytics;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class AnalyticsController extends Controller
{
    /** Brand admins/editors: the whole brand, or one author with ?author=handle. */
    public function brand(Request $request): JsonResponse
    {
        $data = $this->validated($request, withAuthor: true);
        $authorId = isset($data['author']) ? (User::idForHandle($data['author']) ?: abort(404, 'Author not found.')) : null;

        return response()->json((new BrandAnalytics($authorId, $data['range'] ?? '30d'))->report());
    }

    /** Any signed-in author: their own posts in this brand. */
    public function mine(Request $request): JsonResponse
    {
        $data = $this->validated($request, withAuthor: false);

        return response()->json((new BrandAnalytics($request->user()->id, $data['range'] ?? '30d'))->report());
    }

    private function validated(Request $request, bool $withAuthor): array
    {
        return $request->validate(array_filter([
            'range'  => ['nullable', Rule::in(array_keys(BrandAnalytics::RANGES))],
            'author' => $withAuthor ? 'nullable|string|max:100' : null,
        ]));
    }
}
