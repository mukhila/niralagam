<?php

namespace Tests\Feature;

use App\Models\Media;
use App\Models\Post;
use App\Models\Tenant;
use App\Models\User;
use App\Support\Tenancy\CurrentTenant;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class MediaTest extends TestCase
{
    use RefreshDatabase;

    private Tenant $acme;
    private User $author;
    private User $editor;
    private array $h = ['X-Tenant' => 'acme'];

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('public');

        $this->acme   = Tenant::create(['name' => 'Acme', 'slug' => 'acme', 'status' => 'active']);
        $this->author = User::factory()->create(['name' => 'Ava']);
        $this->editor = User::factory()->create();
        $this->acme->users()->attach($this->author->id, ['role' => 'author']);
        $this->acme->users()->attach($this->editor->id, ['role' => 'editor']);
    }

    private function upload(User $as, ?UploadedFile $file = null, array $extra = [])
    {
        Sanctum::actingAs($as);

        return $this->post('/api/media', ['image' => $file ?? UploadedFile::fake()->image('beach.jpg', 3000, 2000)] + $extra,
            $this->h + ['Accept' => 'application/json']);
    }

    public function test_upload_caps_size_and_makes_webp_variants(): void
    {
        $res = $this->upload($this->author, null, ['alt' => 'Sunset on the beach'])->assertCreated()
            ->assertJson(['original_name' => 'beach.jpg', 'mime' => 'image/jpeg', 'width' => 2400, 'height' => 1600,
                'alt' => 'Sunset on the beach', 'uploaded_by' => 'Ava', 'can_manage' => true]);

        $media = Media::withoutGlobalScope('tenant')->first();
        $this->assertSame($this->acme->id, $media->tenant_id);
        $this->assertStringStartsWith("tenants/{$this->acme->id}/media/", $media->path);
        $this->assertSame(['thumb', 'medium', 'large'], array_keys($media->variants));
        $this->assertSame([400, 267], [$media->variants['thumb']['width'], $media->variants['thumb']['height']]);
        $this->assertSame(1600, $media->variants['large']['width']);
        foreach ($media->files() as $path) {
            Storage::disk('public')->assertExists($path);
        }
        $this->assertStringEndsWith('-large.webp', $res->json('urls.large'));
    }

    public function test_small_images_use_the_original_for_bigger_sizes(): void
    {
        $res = $this->upload($this->author, UploadedFile::fake()->image('icon.png', 300, 300))->assertCreated();

        $this->assertSame($res->json('url'), $res->json('urls.large'));
        $this->assertSame($res->json('url'), $res->json('urls.thumb'));
        $this->assertSame('image/png', $res->json('mime'));
    }

    public function test_rejects_svg_non_images_and_non_writers(): void
    {
        $svg = UploadedFile::fake()->createWithContent('x.svg', '<svg xmlns="http://www.w3.org/2000/svg"><script>alert(1)</script></svg>');
        $this->upload($this->author, $svg)->assertUnprocessable();
        $this->upload($this->author, UploadedFile::fake()->create('doc.pdf', 10, 'application/pdf'))->assertUnprocessable();

        $reader = User::factory()->create();
        $this->acme->users()->attach($reader->id, ['role' => 'subscriber']);
        $this->upload($reader)->assertForbidden();
        Sanctum::actingAs($reader);
        $this->getJson('/api/media', $this->h)->assertForbidden();

        $this->assertSame(0, Media::withoutGlobalScope('tenant')->count());
    }

    public function test_library_is_per_brand_and_searchable(): void
    {
        $this->upload($this->author, UploadedFile::fake()->image('beach.jpg', 800, 600), ['alt' => 'Waves']);
        $this->upload($this->editor, UploadedFile::fake()->image('market.jpg', 800, 600));

        Sanctum::actingAs($this->author);
        $this->getJson('/api/media', $this->h)->assertOk()->assertJsonCount(2, 'data');
        $this->getJson('/api/media?search=wave', $this->h)->assertJsonCount(1, 'data')->assertJsonPath('data.0.original_name', 'beach.jpg');
        $this->getJson('/api/media?mine=1', $this->h)->assertJsonCount(1, 'data');
        $this->getJson('/api/media', $this->h)->assertJsonPath('data.0.can_manage', false); // the editor's upload

        $other = Tenant::create(['name' => 'Other', 'slug' => 'other', 'status' => 'active']);
        $other->users()->attach($this->author->id, ['role' => 'author']);
        $this->getJson('/api/media', ['X-Tenant' => 'other'])->assertOk()->assertJsonCount(0, 'data');
    }

    public function test_only_owner_or_editor_can_edit_or_delete(): void
    {
        $id = $this->upload($this->editor, UploadedFile::fake()->image('a.jpg', 800, 600))->json('id');

        Sanctum::actingAs($this->author);
        $this->putJson("/api/media/{$id}", ['alt' => 'x'], $this->h)->assertForbidden();
        $this->deleteJson("/api/media/{$id}", [], $this->h)->assertForbidden();

        $mine = $this->upload($this->author, UploadedFile::fake()->image('b.jpg', 800, 600))->json('id');
        $this->putJson("/api/media/{$mine}", ['alt' => 'My photo'], $this->h)->assertOk()->assertJson(['alt' => 'My photo']);

        Sanctum::actingAs($this->editor);
        $this->putJson("/api/media/{$mine}", ['alt' => 'Edited by editor'], $this->h)->assertOk();
    }

    public function test_delete_warns_when_in_use_and_removes_files(): void
    {
        $res   = $this->upload($this->author, UploadedFile::fake()->image('hero.jpg', 2000, 1000));
        $media = Media::withoutGlobalScope('tenant')->find($res->json('id'));

        app(CurrentTenant::class)->set($this->acme);
        Post::create(['user_id' => $this->author->id, 'title' => 'Uses it', 'slug' => 'uses-it', 'status' => 'draft',
            'content' => '<p>Look</p><img src="'.$res->json('urls.large').'" alt="x">']);
        app(CurrentTenant::class)->set(null);

        Sanctum::actingAs($this->author);
        $this->getJson("/api/media/{$media->id}/usage", $this->h)->assertJsonPath('posts.0.title', 'Uses it');
        $this->deleteJson("/api/media/{$media->id}", [], $this->h)->assertStatus(409)->assertJsonPath('usage.posts.0.slug', 'uses-it');

        $this->deleteJson("/api/media/{$media->id}?force=1", [], $this->h)->assertOk();
        $this->assertNull(Media::withoutGlobalScope('tenant')->find($media->id));
        foreach ($media->files() as $path) {
            Storage::disk('public')->assertMissing($path);
        }
    }

    public function test_legacy_upload_endpoint_goes_into_the_library(): void
    {
        Sanctum::actingAs($this->editor);
        $this->post('/api/upload/image', ['image' => UploadedFile::fake()->image('logo.png', 600, 200)], $this->h + ['Accept' => 'application/json'])
            ->assertCreated()->assertJsonStructure(['id', 'url', 'urls' => ['thumb', 'medium', 'large']]);
        $this->assertSame(1, Media::withoutGlobalScope('tenant')->count());
    }
}
