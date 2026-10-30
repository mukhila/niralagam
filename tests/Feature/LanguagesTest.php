<?php

namespace Tests\Feature;

use App\Models\Post;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class LanguagesTest extends TestCase
{
    use RefreshDatabase;

    private Tenant $acme;
    private User $editor;
    private array $h = ['X-Tenant' => 'acme'];

    protected function setUp(): void
    {
        parent::setUp();
        $this->acme   = Tenant::create(['name' => 'Acme', 'slug' => 'acme', 'status' => 'active',
            'settings' => ['languages' => ['default' => 'ta', 'enabled' => ['ta', 'en', 'ar']]]]);
        $this->editor = User::factory()->create();
        $this->acme->users()->attach($this->editor->id, ['role' => 'editor']);
        Sanctum::actingAs($this->editor);
    }

    private function create(array $body): \Illuminate\Testing\TestResponse
    {
        return $this->postJson('/api/posts', array_merge(['content' => '<p>x</p>', 'status' => 'published'], $body), $this->h);
    }

    public function test_brand_languages_in_settings_and_validation(): void
    {
        $this->getJson('/api/brand', $this->h)->assertOk()
            ->assertJsonPath('languages.default', 'ta')
            ->assertJsonPath('languages.enabled.0', ['code' => 'ta', 'name' => 'Tamil', 'native' => 'தமிழ்', 'dir' => 'ltr'])
            ->assertJsonPath('languages.enabled.2.dir', 'rtl');
        $this->assertGreaterThan(40, count($this->getJson('/api/brand', $this->h)->json('options.languages')));

        $admin = User::factory()->create();
        $this->acme->users()->attach($admin->id, ['role' => 'admin']);
        Sanctum::actingAs($admin);
        $this->putJson('/api/admin/brand', ['languages' => ['default' => 'hi', 'enabled' => ['ta']]], $this->h)->assertOk()
            ->assertJsonPath('languages.default', 'hi')
            ->assertJsonCount(2, 'languages.enabled'); // default is always enabled
        $this->putJson('/api/admin/brand', ['languages' => ['default' => 'xx', 'enabled' => ['ta']]], $this->h)->assertUnprocessable();
    }

    public function test_posts_default_to_brand_language_and_must_use_an_enabled_one(): void
    {
        $this->create(['title' => 'Default'])->assertCreated()->assertJson(['language' => 'ta']);
        $this->create(['title' => 'Arabic', 'language' => 'ar'])->assertCreated()->assertJson(['language' => 'ar']);
        $this->create(['title' => 'Japanese', 'language' => 'ja'])->assertUnprocessable()->assertJsonValidationErrors('language');
    }

    public function test_translations_are_linked_and_listed(): void
    {
        $ta = $this->create(['title' => 'மழை', 'language' => 'ta'])->json('id');
        $en = $this->create(['title' => 'Rain', 'language' => 'en', 'translation_of' => $ta])->assertCreated()->json('id');
        $ar = $this->create(['title' => 'مطر', 'language' => 'ar', 'translation_of' => $en])->assertCreated()->json('id');

        $groups = Post::withoutGlobalScope('tenant')->whereIn('id', [$ta, $en, $ar])->pluck('translation_group')->unique()->all();
        $this->assertSame([$ta], array_values($groups));

        $res = $this->getJson("/api/posts/{$en}", $this->h)->assertOk();
        $this->assertEqualsCanonicalizing(['ta', 'ar'], array_column($res->json('translations'), 'language'));

        // A second version in a language the group already has is refused.
        $this->create(['title' => 'Rain again', 'language' => 'en', 'translation_of' => $ta])->assertUnprocessable();
        $this->putJson("/api/posts/{$ar}", ['language' => 'en'], $this->h)->assertUnprocessable();
        $this->putJson("/api/posts/{$ar}", ['translation_of' => $ta], $this->h)->assertUnprocessable(); // can't relink by update
    }

    public function test_readers_only_see_live_translations_and_can_filter_by_language(): void
    {
        $ta = $this->create(['title' => 'மழை', 'language' => 'ta'])->json('id');
        $this->create(['title' => 'Rain draft', 'language' => 'en', 'translation_of' => $ta, 'status' => 'draft']);
        $this->create(['title' => 'مطر', 'language' => 'ar', 'translation_of' => $ta]);

        $this->app['auth']->forgetGuards();
        $res = $this->getJson('/api/posts/'.Post::withoutGlobalScope('tenant')->find($ta)->slug, $this->h)->assertOk();
        $this->assertSame(['ar'], array_column($res->json('translations'), 'language'));

        $this->getJson('/api/posts?status=published&language=ar', $this->h)->assertJsonCount(1, 'data')->assertJsonPath('data.0.title', 'مطر');
        $this->getJson('/api/search?q=%D9%85%D8%B7%D8%B1&language=ar', $this->h)->assertJson(['total' => 1]);
        $this->getJson('/api/search?q=%D9%85%D8%B7%D8%B1&language=ta', $this->h)->assertJson(['total' => 0]);
    }

    public function test_share_card_carries_the_post_locale(): void
    {
        $id   = $this->create(['title' => 'مطر', 'language' => 'ar'])->json('id');
        $slug = Post::withoutGlobalScope('tenant')->find($id)->slug;

        $this->get('/api/share?path=/blog/'.rawurlencode($slug), $this->h)->assertOk()
            ->assertSee('<meta property="og:locale" content="ar">', false);
        $this->get('/api/share?path=/', $this->h)->assertSee('<meta property="og:locale" content="ta">', false);
    }
}
