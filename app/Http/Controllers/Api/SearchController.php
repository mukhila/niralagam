<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Post;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Public search over the current brand's live posts.
 *
 * MySQL/MariaDB: FULLTEXT in boolean mode with prefix matching, ranked with
 * title matches weighted ×3. Elsewhere (SQLite in tests), or when every word is
 * shorter than the index's minimum, it falls back to LIKE matching of all words.
 */
class SearchController extends Controller
{
    private const MIN_FULLTEXT_WORD = 3; // innodb_ft_min_token_size default
    private const MAX_TERMS = 8;
    private const SNIPPET = 180;

    public function search(Request $request): JsonResponse
    {
        $data = $request->validate([
            'q'        => 'required|string|max:100',
            'category' => 'nullable|string|max:100',
            'tag'      => 'nullable|string|max:100',
            'author'   => 'nullable|string|max:100',
            'language' => 'nullable|string|max:10',
            'from'     => 'nullable|date',
            'to'       => 'nullable|date',
            'sort'     => 'nullable|in:relevance,newest,oldest',
            'per_page' => 'nullable|integer|min:1|max:50',
        ]);

        $terms = $this->terms($data['q']);
        if (! $terms) {
            return response()->json(['data' => [], 'total' => 0, 'terms' => [], 'facets' => ['categories' => []]]);
        }

        $base = Post::live()
            ->when($data['tag'] ?? null, fn ($q, $t) => $q->whereHas('tags', fn ($q) => $q->where('slug', $t)))
            ->when($data['author'] ?? null, fn ($q, $a) => $q->where('user_id', User::idForHandle($a)))
            ->when($data['language'] ?? null, fn ($q, $l) => $q->where('language', $l))
            ->when($data['from'] ?? null, fn ($q, $d) => $q->where('published_at', '>=', $d))
            ->when($data['to'] ?? null, fn ($q, $d) => $q->where('published_at', '<', now()->parse($d)->addDay()));

        $useFulltext = $this->fulltextAvailable() && collect($terms)->contains(fn ($t) => mb_strlen($t) >= self::MIN_FULLTEXT_WORD);
        $this->match($base, $terms, $useFulltext);

        // Facets ignore the category filter, so readers can switch between categories.
        $facets = (clone $base)->reorder()
            ->join('categories', 'categories.id', '=', 'posts.category_id')
            ->groupBy('categories.id', 'categories.name', 'categories.slug')
            ->selectRaw('categories.name, categories.slug, COUNT(*) as count')
            ->orderByDesc('count')
            ->limit(20)
            ->get();

        $query = (clone $base)
            ->with(['category:id,name,slug', 'user:id,name,username'])
            ->when($data['category'] ?? null, fn ($q, $c) => $q->whereHas('category', fn ($q) => $q->where('slug', $c)));

        match ($data['sort'] ?? 'relevance') {
            'newest'    => $query->reorder()->orderByDesc('published_at'),
            'oldest'    => $query->reorder()->orderBy('published_at'),
            'relevance' => $query->orderByDesc('published_at'), // after the score ordering added by match()
        };

        $page = $query->paginate((int) ($data['per_page'] ?? 10))->withQueryString();

        $page->getCollection()->transform(fn (Post $p) => [
            ...$p->only('id', 'title', 'slug', 'excerpt', 'featured_image', 'published_at'),
            'category' => $p->category?->only('name', 'slug'),
            'user'     => $p->user?->only('id', 'name', 'username'),
            'snippet'  => $this->snippet($p->meta_description ?: $p->search_text ?: $p->excerpt ?: '', $terms),
        ]);

        return response()->json([
            ...$page->toArray(),
            'terms'  => $terms,
            'facets' => ['categories' => $facets->map(fn ($f) => ['name' => $f->name, 'slug' => $f->slug, 'count' => (int) $f->count])],
        ]);
    }

    /** Words from the query, without boolean-mode operators. */
    private function terms(string $q): array
    {
        $clean = preg_replace('/[+\-<>()~*"@]+/u', ' ', Str::lower($q));

        return array_values(array_slice(array_unique(array_filter(preg_split('/\s+/u', trim($clean)))), 0, self::MAX_TERMS));
    }

    private function fulltextAvailable(): bool
    {
        return in_array(DB::getDriverName(), ['mysql', 'mariadb'], true);
    }

    private function match(Builder $query, array $terms, bool $fulltext): void
    {
        if ($fulltext) {
            // Each word as a prefix ("monsoon*" also finds "monsoons"); any word may match, more matches rank higher.
            $against = implode(' ', array_map(fn ($t) => $t.'*', array_filter($terms, fn ($t) => mb_strlen($t) >= self::MIN_FULLTEXT_WORD)));

            $query->whereRaw('MATCH(posts.title, posts.excerpt, posts.search_text) AGAINST (? IN BOOLEAN MODE)', [$against])
                ->orderByRaw(
                    'MATCH(posts.title) AGAINST (? IN BOOLEAN MODE) * 3 + MATCH(posts.title, posts.excerpt, posts.search_text) AGAINST (? IN BOOLEAN MODE) DESC',
                    [$against, $against],
                );

            return;
        }

        // Fallback: every word must appear somewhere; title hits first.
        foreach ($terms as $term) {
            $like = '%'.addcslashes($term, '%_\\').'%';
            $query->where(fn ($q) => $q->where('posts.title', 'like', $like)
                ->orWhere('posts.excerpt', 'like', $like)
                ->orWhere('posts.search_text', 'like', $like));
        }
        $first = '%'.addcslashes($terms[0], '%_\\').'%';
        $query->orderByRaw('CASE WHEN posts.title LIKE ? THEN 0 ELSE 1 END', [$first]);
    }

    /** ~180 characters of plain text around the first matching word. */
    private function snippet(string $text, array $terms): string
    {
        if ($text === '') {
            return '';
        }

        $lower = mb_strtolower($text);
        $pos   = collect($terms)->map(fn ($t) => mb_strpos($lower, $t))->filter(fn ($p) => $p !== false)->min();

        if ($pos === null || mb_strlen($text) <= self::SNIPPET) {
            return Str::limit($text, self::SNIPPET);
        }

        $start = max(0, $pos - 60);
        // Start at a word boundary.
        if ($start > 0 && ($space = mb_strpos($text, ' ', $start)) !== false && $space < $pos) {
            $start = $space + 1;
        }
        $piece = mb_substr($text, $start, self::SNIPPET);

        return ($start > 0 ? '…' : '').rtrim($piece).($start + self::SNIPPET < mb_strlen($text) ? '…' : '');
    }
}
