<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Post;
use App\Models\Tenant;
use App\Models\User;
use App\Support\Tenancy\CurrentTenant;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/** Runs on SQLite, so it exercises the LIKE fallback; MySQL uses FULLTEXT with the same API. */
class SearchTest extends TestCase
{
    use RefreshDatabase;

    private Tenant $acme;
    private User $author;
    private array $h = ['X-Tenant' => 'acme'];

    protected function setUp(): void
    {
        parent::setUp();
        $this->acme   = Tenant::create(['name' => 'Acme', 'slug' => 'acme', 'status' => 'active']);
        $this->author = User::factory()->create(['username' => 'ava', 'name' => 'Ava']);
    }

    private function inAcme(callable $fn)
    {
        app(CurrentTenant::class)->set($this->acme);
        try {
            return $fn();
        } finally {
            app(CurrentTenant::class)->set(null);
        }
    }

    private function makePost(string $title, string $content, array $extra = []): Post
    {
        return $this->inAcme(fn () => Post::create(array_merge([
            'user_id' => $this->author->id, 'title' => $title, 'slug' => Post::uniqueSlug($title), 'content' => $content,
            'status' => 'published', 'published_at' => now()->subDay(),
        ], $extra)));
    }

    public function test_search_text_is_plain_text_kept_in_sync(): void
    {
        $post = $this->makePost('T', '<p>Hello<strong>world</strong></p><p>Second&nbsp;para &amp; more</p>');
        $this->assertSame('Hello world Second para & more', $post->search_text);
        $this->assertArrayNotHasKey('search_text', $post->toArray());

        $post->update(['content' => '<p>Changed</p>']);
        $this->assertSame('Changed', $post->fresh()->search_text);
    }

    public function test_finds_words_in_body_not_html_and_ranks_title_first(): void
    {
        $this->makePost('Kitchen notes', '<p>A recipe for monsoon tea.</p>');
        $this->makePost('Monsoon diaries', '<p>Rain all week.</p>');
        $this->makePost('Unrelated', '<p><strong>strong</strong> coffee <a href="https://example.test">link</a></p>');

        $res = $this->getJson('/api/search?q=monsoon', $this->h)->assertOk()->assertJson(['total' => 2, 'terms' => ['monsoon']]);
        $this->assertSame(['Monsoon diaries', 'Kitchen notes'], array_column($res->json('data'), 'title'));
        $this->assertSame('A recipe for monsoon tea.', $res->json('data.1.snippet'));

        // Markup isn't searchable: "strong" only matches the visible word.
        $this->getJson('/api/search?q=strong', $this->h)->assertJson(['total' => 1]);
        $this->getJson('/api/search?q=href', $this->h)->assertJson(['total' => 0]);
        $this->getJson('/api/search?q=example.test', $this->h)->assertJson(['total' => 0]);
    }

    public function test_only_live_posts_of_this_brand(): void
    {
        $this->makePost('Rain live', '<p>x</p>');
        $this->makePost('Rain draft', '<p>x</p>', ['status' => 'draft']);
        $this->makePost('Rain later', '<p>x</p>', ['status' => 'scheduled', 'published_at' => now()->addDay()]);

        $other = Tenant::create(['name' => 'Other', 'slug' => 'other', 'status' => 'active']);
        app(CurrentTenant::class)->set($other);
        Post::create(['user_id' => $this->author->id, 'title' => 'Rain elsewhere', 'slug' => 'rain-elsewhere', 'content' => 'x', 'status' => 'published', 'published_at' => now()]);
        app(CurrentTenant::class)->set(null);

        $res = $this->getJson('/api/search?q=rain', $this->h);
        $this->assertSame(['Rain live'], array_column($res->json('data'), 'title'));
    }

    public function test_filters_facets_and_sorting(): void
    {
        [$travel, $food] = $this->inAcme(fn () => [
            Category::create(['name' => 'Travel', 'slug' => 'travel']),
            Category::create(['name' => 'Food', 'slug' => 'food']),
        ]);
        $this->makePost('Chennai trip', '<p>coast</p>', ['category_id' => $travel->id, 'published_at' => now()->subDays(3)]);
        $this->makePost('Chennai beaches', '<p>coast</p>', ['category_id' => $travel->id, 'published_at' => now()->subDay()]);
        $this->makePost('Chennai dosa', '<p>coast</p>', ['category_id' => $food->id, 'published_at' => now()->subDays(2)]);

        $res = $this->getJson('/api/search?q=chennai&category=food', $this->h)->assertJson(['total' => 1]);
        // Facets ignore the selected category so readers can switch.
        $this->assertSame([['name' => 'Travel', 'slug' => 'travel', 'count' => 2], ['name' => 'Food', 'slug' => 'food', 'count' => 1]], $res->json('facets.categories'));

        $newest = $this->getJson('/api/search?q=chennai&sort=newest', $this->h)->json('data');
        $this->assertSame(['Chennai beaches', 'Chennai dosa', 'Chennai trip'], array_column($newest, 'title'));

        $this->getJson('/api/search?q=chennai&author=ava', $this->h)->assertJson(['total' => 3]);
        $this->getJson('/api/search?q=chennai&author=nobody', $this->h)->assertJson(['total' => 0]);
        $this->getJson('/api/search?q=chennai&from='.now()->subDays(2)->toDateString(), $this->h)->assertJson(['total' => 2]);
    }

    public function test_all_words_must_match_and_operators_are_ignored(): void
    {
        $this->makePost('Slow travel in India', '<p>trains</p>');
        $this->makePost('Fast food', '<p>burgers</p>');

        $this->getJson('/api/search?q=slow+india', $this->h)->assertJson(['total' => 1]);
        $this->getJson('/api/search?q='.urlencode('+india -slow "('), $this->h)->assertOk()->assertJson(['terms' => ['india', 'slow']]);
        $this->getJson('/api/search?q='.urlencode('100%_'), $this->h)->assertOk()->assertJson(['total' => 0]);
        $this->getJson('/api/search', $this->h)->assertUnprocessable();
    }

    public function test_snippet_centres_on_the_match(): void
    {
        $long = str_repeat('Lorem ipsum dolor sit amet. ', 30).'The hidden keyword lives here. '.str_repeat('More text after. ', 20);
        $this->makePost('Long one', "<p>{$long}</p>");

        $snippet = $this->getJson('/api/search?q=keyword', $this->h)->json('data.0.snippet');
        $this->assertStringStartsWith('…', $snippet);
        $this->assertStringContainsString('hidden keyword', $snippet);
        $this->assertLessThanOrEqual(184, mb_strlen($snippet));
    }
}
