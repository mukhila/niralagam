<?php

namespace Tests\Feature;

use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class BrandSettingsTest extends TestCase
{
    use RefreshDatabase;

    private Tenant $acme;
    private User $admin;
    private array $h = ['X-Tenant' => 'acme'];

    protected function setUp(): void
    {
        parent::setUp();
        $this->acme  = Tenant::create(['name' => 'Acme', 'slug' => 'acme', 'status' => 'active', 'settings' => ['allow_public_posts' => true]]);
        $this->admin = User::factory()->create();
        $this->acme->users()->attach($this->admin->id, ['role' => 'admin']);
    }

    public function test_public_brand_endpoint_returns_defaults(): void
    {
        $this->getJson('/api/brand', $this->h)->assertOk()->assertJson([
            'name'  => 'Acme',
            'slug'  => 'acme',
            'theme' => ['home_layout' => 'magazine', 'heading_font' => 'system', 'primary_color' => null],
        ])->assertJsonPath('options.layouts', ['magazine', 'grid']);

        $this->getJson('/api/brand')->assertOk()->assertJson(['slug' => 'main']);
    }

    public function test_brand_admin_updates_the_theme(): void
    {
        Sanctum::actingAs($this->admin);

        $this->putJson('/api/admin/brand', [
            'name'          => 'Acme Daily',
            'logo'          => 'https://cdn.example.test/logo.png',
            'tagline'       => 'News that matters',
            'hero_title'    => 'Hello Acme',
            'primary_color' => '#ff5500',
            'accent_color'  => '#112233',
            'heading_font'  => 'Playfair Display',
            'body_font'     => 'Noto Sans Tamil',
            'home_layout'   => 'grid',
            'social'        => ['twitter' => 'https://x.com/acme', 'facebook' => null],
        ], $this->h)->assertOk()->assertJson([
            'name'  => 'Acme Daily',
            'logo'  => 'https://cdn.example.test/logo.png',
            'theme' => ['primary_color' => '#ff5500', 'home_layout' => 'grid', 'social' => ['twitter' => 'https://x.com/acme']],
        ]);

        $fresh = $this->acme->fresh();
        $this->assertSame('Acme Daily', $fresh->name);
        $this->assertTrue($fresh->allowsPublicPosts(), 'other settings keys are preserved');

        // Partial updates merge; the public endpoint reflects them.
        $this->putJson('/api/admin/brand', ['tagline' => 'New tagline'], $this->h)->assertOk();
        $this->getJson('/api/brand', $this->h)->assertJson(['theme' => ['tagline' => 'New tagline', 'primary_color' => '#ff5500']]);
    }

    public function test_unsafe_values_are_rejected(): void
    {
        Sanctum::actingAs($this->admin);

        foreach ([
            ['primary_color' => 'red; background:url(x)'],
            ['accent_color' => '#12345'],
            ['heading_font' => 'Comic Sans"; } body { display:none'],
            ['home_layout' => 'carousel'],
            ['logo' => 'javascript:alert(1)'],
            ['social' => ['twitter' => 'javascript:alert(1)']],
            ['social' => ['myspace' => 'https://myspace.com/acme']],
        ] as $bad) {
            $this->putJson('/api/admin/brand', $bad, $this->h)->assertUnprocessable();
        }
    }

    public function test_only_brand_admins_can_edit(): void
    {
        $editor = User::factory()->create();
        $this->acme->users()->attach($editor->id, ['role' => 'editor']);
        Sanctum::actingAs($editor);
        $this->putJson('/api/admin/brand', ['tagline' => 'x'], $this->h)->assertForbidden();

        // An admin of Acme can't edit another brand.
        Tenant::create(['name' => 'Other', 'slug' => 'other', 'status' => 'active']);
        Sanctum::actingAs($this->admin);
        $this->putJson('/api/admin/brand', ['tagline' => 'x'], ['X-Tenant' => 'other'])->assertForbidden();
    }
}
