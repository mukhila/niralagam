<?php

namespace Tests\Feature;

use App\Models\Post;
use App\Models\Tenant;
use App\Models\User;
use App\Support\Tenancy\CurrentTenant;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SharePreviewTest extends TestCase
{
    use RefreshDatabase;

    private Tenant $acme;
    private array $h = ['X-Tenant' => 'acme'];

    protected function setUp(): void
    {
        parent::setUp();
        config(['tenancy.frontend_url' => 'https://niralagam.test', 'tenancy.central_domain' => 'niralagam.test']);
        $this->acme = Tenant::create(['name' => 'Acme', 'slug' => 'acme', 'status' => 'active', 'logo' => 'https://cdn.test/logo.png',
            'settings' => ['theme' => ['about' => 'Coastal stories']]]);
    }

    private function makePost(array $attrs): Post
    {
        app(CurrentTenant::class)->set($this->acme);
        try {
            return Post::create(array_merge([
                'user_id' => User::factory()->create(['name' => 'Ava'])->id, 'title' => 'Monsoon', 'slug' => 'monsoon',
                'content' => '<p>The rains arrive in October.</p>', 'status' => 'published', 'published_at' => now()->subHour(),
            ], $attrs));
        } finally {
            app(CurrentTenant::class)->set(null);
        }
    }

    /** @return array<string,string> og/twitter/article tags from the HTML */
    private function tags(string $html): array
    {
        preg_match_all('/<meta (?:property|name)="([^"]+)" content="([^"]*)">/', $html, $m);

        return array_combine($m[1], array_map(fn ($v) => html_entity_decode($v, ENT_QUOTES), $m[2]));
    }

    public function test_post_card_uses_seo_fields_then_falls_back(): void
    {
        $this->makePost(['meta_title' => 'Monsoon guide', 'meta_description' => 'Everything about the rains', 'og_image' => 'https://cdn.test/share.jpg']);

        $tags = $this->tags($this->get('/api/share?path=/blog/monsoon', $this->h)->assertOk()->getContent());

        $this->assertSame('Monsoon guide', $tags['og:title']);
        $this->assertSame('Everything about the rains', $tags['og:description']);
        $this->assertSame('https://cdn.test/share.jpg', $tags['og:image']);
        $this->assertSame('https://acme.niralagam.test/blog/monsoon', $tags['og:url']);
        $this->assertSame('article', $tags['og:type']);
        $this->assertSame('summary_large_image', $tags['twitter:card']);
        $this->assertSame('Ava', $tags['article:author']);
    }

    public function test_post_without_seo_fields_uses_content_and_logo(): void
    {
        $this->makePost([]);

        $tags = $this->tags($this->get('/api/share?path=/blog/monsoon/', $this->h)->assertOk()->getContent());

        $this->assertSame('Monsoon', $tags['og:title']);
        $this->assertSame('The rains arrive in October.', $tags['og:description']);
        $this->assertSame('https://cdn.test/logo.png', $tags['og:image']);
    }

    public function test_unpublished_posts_never_leak(): void
    {
        $this->makePost(['status' => 'draft', 'title' => 'Secret plans', 'content' => '<p>Top secret</p>']);

        $html = $this->get('/api/share?path=/blog/monsoon', $this->h)->assertNotFound()->getContent();

        $this->assertStringNotContainsString('Secret', $html);
        $this->assertSame('Acme', $this->tags($html)['og:title']);
    }

    public function test_brand_pages_get_the_brand_card(): void
    {
        $home = $this->tags($this->get('/api/share?path=/', $this->h)->assertOk()->getContent());
        $this->assertSame(['Acme', 'Coastal stories', 'website', 'https://acme.niralagam.test/'],
            [$home['og:title'], $home['og:description'], $home['og:type'], $home['og:url']]);

        $blog = $this->tags($this->get('/api/share?path=/blog', $this->h)->getContent());
        $this->assertSame('Blog · Acme', $blog['og:title']);
    }

    public function test_values_are_html_escaped(): void
    {
        $this->makePost(['title' => '"><script>alert(1)</script>', 'excerpt' => 'Fish & chips']);

        $html = $this->get('/api/share?path=/blog/monsoon', $this->h)->getContent();

        $this->assertStringNotContainsString('<script>', $html);
        $this->assertStringContainsString('Fish &amp; chips', $html);
    }
}
