<?php

namespace Tests\Feature;

use App\Models\Post;
use App\Models\Tenant;
use App\Models\User;
use App\Support\Tenancy\CurrentTenant;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class SeoTest extends TestCase
{
    use RefreshDatabase;

    private Tenant $acme;
    private array $h = ['X-Tenant' => 'acme'];

    protected function setUp(): void
    {
        parent::setUp();
        config(['tenancy.frontend_url' => 'https://niralagam.test', 'tenancy.central_domain' => 'niralagam.test']);
        $this->acme = Tenant::create(['name' => 'Acme <News>', 'slug' => 'acme', 'status' => 'active',
            'settings' => ['theme' => ['about' => 'Coastal stories']]]);
    }

    private function makePost(string $title, string $status = 'published', array $extra = []): Post
    {
        app(CurrentTenant::class)->set($this->acme);
        try {
            return Post::create(array_merge([
                'user_id' => User::factory()->create(['name' => 'Ava'])->id, 'title' => $title, 'slug' => Post::uniqueSlug($title),
                'content' => '<p>Body of '.$title.'</p>', 'status' => $status, 'published_at' => now()->subHour(),
            ], $extra));
        } finally {
            app(CurrentTenant::class)->set(null);
        }
    }

    public function test_public_url_follows_domain_subdomain_or_default(): void
    {
        $this->assertSame('https://acme.niralagam.test', $this->acme->publicUrl());
        $this->acme->update(['domain' => 'blog.acme.test']);
        $this->assertSame('https://blog.acme.test', $this->acme->publicUrl());
        $this->assertSame('https://niralagam.test', Tenant::default()->publicUrl());
    }

    public function test_sitemap_lists_only_live_posts_of_the_brand(): void
    {
        $this->makePost('Live one');
        $this->makePost('Secret draft', 'draft');
        $this->makePost('Future', 'scheduled', ['published_at' => now()->addDay()]);

        $res = $this->get('/api/sitemap.xml', $this->h)->assertOk()->assertHeader('Content-Type', 'application/xml; charset=UTF-8');
        $xml = simplexml_load_string($res->getContent());
        $locs = array_map(fn ($u) => (string) $u->loc, iterator_to_array($xml->url, false));

        $this->assertContains('https://acme.niralagam.test/blog/live-one', $locs);
        $this->assertContains('https://acme.niralagam.test/', $locs);
        $this->assertNotContains('https://acme.niralagam.test/blog/secret-draft', $locs);
        $this->assertNotContains('https://acme.niralagam.test/blog/future', $locs);

        // Another brand's sitemap doesn't include Acme's posts.
        $this->assertStringNotContainsString('live-one', $this->get('/api/sitemap.xml')->getContent());
    }

    public function test_rss_feed_is_valid_and_escaped(): void
    {
        $this->makePost('Fish & <Chips>', 'published', ['excerpt' => 'A summary']);

        $res = $this->get('/api/rss.xml', $this->h)->assertOk()->assertHeader('Content-Type', 'application/rss+xml; charset=UTF-8');
        $xml = simplexml_load_string($res->getContent());

        $this->assertNotFalse($xml, 'feed must be well-formed XML');
        $this->assertSame('Acme <News>', (string) $xml->channel->title);
        $this->assertSame('Coastal stories', (string) $xml->channel->description);
        $this->assertSame('Fish & <Chips>', (string) $xml->channel->item[0]->title);
        $this->assertSame('A summary', (string) $xml->channel->item[0]->description);
    }

    public function test_robots_points_at_the_brand_sitemap(): void
    {
        $this->get('/api/robots.txt', $this->h)->assertOk()
            ->assertSee('Disallow: /admin')
            ->assertSee('Sitemap: https://acme.niralagam.test/sitemap.xml');
    }

    public function test_editors_can_set_seo_fields(): void
    {
        $editor = User::factory()->create();
        $this->acme->users()->attach($editor->id, ['role' => 'editor']);
        Sanctum::actingAs($editor);

        $this->postJson('/api/posts', [
            'title' => 'T', 'content' => 'c', 'meta_title' => '<b>Better</b> title',
            'meta_description' => 'Short and sweet', 'og_image' => 'https://img.test/a.png',
        ], $this->h)->assertCreated()->assertJson([
            'meta_title' => 'Better title', 'meta_description' => 'Short and sweet', 'og_image' => 'https://img.test/a.png',
        ]);

        $this->postJson('/api/posts', ['title' => 'T', 'content' => 'c', 'meta_description' => str_repeat('x', 171)], $this->h)->assertUnprocessable();
        $this->postJson('/api/posts', ['title' => 'T', 'content' => 'c', 'og_image' => 'javascript:x'], $this->h)->assertUnprocessable();
    }
}
