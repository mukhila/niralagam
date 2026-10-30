<?php

namespace Tests\Feature;

use App\Mail\NewPostAlertMail;
use App\Models\Post;
use App\Models\Tenant;
use App\Models\User;
use App\Support\Tenancy\CurrentTenant;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class FollowTest extends TestCase
{
    use RefreshDatabase;

    private Tenant $acme;
    private User $author;
    private User $editor;
    private array $h = ['X-Tenant' => 'acme'];

    protected function setUp(): void
    {
        parent::setUp();
        Mail::fake();
        config(['tenancy.frontend_url' => 'https://niralagam.test', 'tenancy.central_domain' => 'niralagam.test']);

        $this->acme   = Tenant::create(['name' => 'Acme', 'slug' => 'acme', 'status' => 'active']);
        $this->author = User::factory()->create(['name' => 'Ava Author', 'username' => 'ava', 'bio' => 'Writes about rain']);
        $this->editor = User::factory()->create();
        $this->acme->users()->attach($this->author->id, ['role' => 'author']);
        $this->acme->users()->attach($this->editor->id, ['role' => 'editor']);
    }

    private function makePost(User $by, string $title, string $status = 'published'): Post
    {
        app(CurrentTenant::class)->set($this->acme);
        try {
            return Post::create([
                'user_id' => $by->id, 'title' => $title, 'slug' => Post::uniqueSlug($title), 'content' => '<p>x</p>',
                'status' => $status, 'published_at' => $status === 'published' ? now()->subMinute() : null,
            ]);
        } finally {
            app(CurrentTenant::class)->set(null);
        }
    }

    public function test_author_page_exists_only_after_publishing_in_this_brand(): void
    {
        $this->getJson('/api/authors/ava', $this->h)->assertNotFound();
        $this->makePost($this->author, 'Draft', 'draft');
        $this->getJson('/api/authors/ava', $this->h)->assertNotFound();

        $this->makePost($this->author, 'Rain');
        $this->getJson('/api/authors/ava', $this->h)->assertOk()->assertJson([
            'name' => 'Ava Author', 'handle' => 'ava', 'bio' => 'Writes about rain', 'posts_count' => 1, 'followers_count' => 0,
        ])->assertJsonMissing(['email' => $this->author->email]);

        // Not in another brand, and users without a username use their id.
        Tenant::create(['name' => 'Other', 'slug' => 'other', 'status' => 'active']);
        $this->getJson('/api/authors/ava', ['X-Tenant' => 'other'])->assertNotFound();

        $noName = User::factory()->create(['username' => null]);
        $this->makePost($noName, 'By id');
        $this->getJson("/api/authors/{$noName->id}", $this->h)->assertOk()->assertJson(['handle' => (string) $noName->id]);

        // ?author= filters the post list.
        $this->getJson('/api/posts?author=ava&status=published', $this->h)->assertJsonCount(1, 'data')->assertJsonPath('data.0.user.username', 'ava');
    }

    public function test_follow_and_unfollow_author_and_brand(): void
    {
        $this->makePost($this->author, 'Rain');
        $reader = User::factory()->create();
        Sanctum::actingAs($reader);

        $this->postJson('/api/authors/ava/follow', [], $this->h)->assertOk()->assertJson(['following' => true, 'followers_count' => 1]);
        $this->postJson('/api/brand/follow', [], $this->h)->assertOk()->assertJson(['following' => true]);
        $this->getJson('/api/authors/ava', $this->h)->assertJson(['is_following' => true, 'followers_count' => 1]);
        $this->getJson('/api/me/follows', $this->h)->assertJson(['brand' => true, 'authors' => [['handle' => 'ava', 'name' => 'Ava Author']]]);

        $this->postJson('/api/authors/ava/follow', [], $this->h)->assertOk()->assertJson(['following' => false, 'followers_count' => 0]);
        $this->getJson('/api/me/follows', $this->h)->assertJson(['brand' => true, 'authors' => []]);

        // Follows are per brand.
        Tenant::create(['name' => 'Other', 'slug' => 'other', 'status' => 'active']);
        $this->getJson('/api/me/follows', ['X-Tenant' => 'other'])->assertJson(['brand' => false]);
    }

    public function test_following_needs_sign_in_and_not_yourself(): void
    {
        $this->makePost($this->author, 'Rain');

        $this->postJson('/api/authors/ava/follow', [], $this->h)->assertUnauthorized();

        Sanctum::actingAs($this->author);
        $this->postJson('/api/authors/ava/follow', [], $this->h)->assertUnprocessable();
    }

    public function test_followers_get_one_alert_when_a_post_goes_live(): void
    {
        $this->makePost($this->author, 'First');
        $brandFan  = User::factory()->create();
        $authorFan = User::factory()->create();
        $both      = User::factory()->create();
        foreach ([[$brandFan, 'brand'], [$authorFan, 'author'], [$both, 'brand'], [$both, 'author']] as [$u, $what]) {
            Sanctum::actingAs($u);
            $this->postJson($what === 'brand' ? '/api/brand/follow' : '/api/authors/ava/follow', [], $this->h)->assertOk();
        }

        // Author submits; nothing is sent until an editor publishes.
        Sanctum::actingAs($this->author);
        $id = $this->postJson('/api/posts', ['title' => 'Second', 'content' => 'c', 'status' => 'pending'], $this->h)->json('id');
        Mail::assertNotQueued(NewPostAlertMail::class);

        Sanctum::actingAs($this->editor);
        $this->postJson("/api/posts/{$id}/approve", [], $this->h)->assertOk();

        Mail::assertQueued(NewPostAlertMail::class, 3); // $both gets one, not two
        foreach ([$brandFan, $authorFan, $both] as $u) {
            Mail::assertQueued(NewPostAlertMail::class, fn ($m) => $m->hasTo($u->email) && $m->post->title === 'Second');
        }
        Mail::assertNotQueued(NewPostAlertMail::class, fn ($m) => $m->hasTo($this->author->email));

        // Unpublishing and re-publishing doesn't alert again.
        $this->putJson("/api/posts/{$id}", ['status' => 'draft'], $this->h)->assertOk();
        $this->putJson("/api/posts/{$id}", ['status' => 'published'], $this->h)->assertOk();
        Mail::assertQueued(NewPostAlertMail::class, 3);
    }

    public function test_scheduled_posts_alert_when_the_scheduler_publishes_them(): void
    {
        $this->makePost($this->author, 'First');
        $fan = User::factory()->create();
        Sanctum::actingAs($fan);
        $this->postJson('/api/brand/follow', [], $this->h);

        Sanctum::actingAs($this->editor);
        $this->postJson('/api/posts', ['title' => 'Later', 'content' => 'c', 'status' => 'scheduled', 'publish_at' => now()->addHour()->toIso8601String()], $this->h)
            ->assertCreated();
        Mail::assertNotQueued(NewPostAlertMail::class);

        $this->travel(61)->minutes();
        $this->artisan('posts:publish-scheduled')->assertSuccessful();
        Mail::assertQueued(NewPostAlertMail::class, fn ($m) => $m->hasTo($fan->email) && $m->post->title === 'Later');
    }

    public function test_alert_email_renders_with_links(): void
    {
        $post = $this->makePost($this->author, 'Rain & sun');
        $html = (new NewPostAlertMail($post->fresh(['user', 'tenant']), User::factory()->create()))->render();

        $this->assertStringContainsString('https://acme.niralagam.test/blog/rain-sun', $html);
        $this->assertStringContainsString('https://acme.niralagam.test/author/ava', $html);
        $this->assertStringContainsString('https://acme.niralagam.test/user/following', $html);
        $this->assertStringContainsString('Rain &amp; sun', $html);
    }
}
