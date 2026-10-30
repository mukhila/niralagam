<?php

namespace Tests\Feature;

use App\Mail\PostWorkflowMail;
use App\Models\Post;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class EditorialWorkflowTest extends TestCase
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

        $this->acme   = Tenant::create(['name' => 'Acme', 'slug' => 'acme', 'status' => 'active']);
        $this->author = User::factory()->create(['name' => 'Ava Author']);
        $this->editor = User::factory()->create(['name' => 'Ed Editor']);
        $this->acme->users()->attach($this->author->id, ['role' => 'author']);
        $this->acme->users()->attach($this->editor->id, ['role' => 'editor']);
    }

    private function draft(array $attrs = []): int
    {
        Sanctum::actingAs($this->author);

        return $this->postJson('/api/posts', array_merge(['title' => 'My story', 'content' => 'v1'], $attrs), $this->h)
            ->assertCreated()->json('id');
    }

    public function test_authors_cannot_publish_or_schedule_directly(): void
    {
        Sanctum::actingAs($this->author);

        $this->postJson('/api/posts', ['title' => 'T', 'content' => 'c', 'status' => 'published'], $this->h)->assertUnprocessable();
        $this->postJson('/api/posts', ['title' => 'T', 'content' => 'c', 'status' => 'scheduled', 'publish_at' => now()->addDay()], $this->h)->assertUnprocessable();
        $this->postJson('/api/posts', ['title' => 'T', 'content' => 'c', 'status' => 'pending'], $this->h)->assertCreated();
    }

    public function test_full_review_cycle_with_notifications(): void
    {
        $id = $this->draft();

        // Submit → editors are emailed, the author isn't.
        $this->postJson("/api/posts/{$id}/submit", [], $this->h)->assertOk()->assertJson(['status' => 'pending']);
        Mail::assertSent(PostWorkflowMail::class, fn ($m) => $m->event === 'submitted' && $m->hasTo($this->editor->email));
        Mail::assertNotSent(PostWorkflowMail::class, fn ($m) => $m->hasTo($this->author->email));

        // Editor sees it in the review queue; the public doesn't.
        Sanctum::actingAs($this->editor);
        $this->getJson('/api/posts?status=pending', $this->h)->assertJsonCount(1, 'data');

        // Reject needs a note; the author gets it by email.
        $this->postJson("/api/posts/{$id}/reject", [], $this->h)->assertUnprocessable();
        $this->postJson("/api/posts/{$id}/reject", ['note' => 'Add sources, please.'], $this->h)
            ->assertOk()->assertJson(['status' => 'rejected', 'review_note' => 'Add sources, please.', 'reviewer' => ['name' => 'Ed Editor']]);
        Mail::assertSent(PostWorkflowMail::class, fn ($m) => $m->event === 'rejected' && $m->hasTo($this->author->email) && $m->note === 'Add sources, please.');

        // Author fixes it and resubmits; the old note is cleared.
        Sanctum::actingAs($this->author);
        $this->putJson("/api/posts/{$id}", ['content' => 'v2 with sources', 'status' => 'pending'], $this->h)
            ->assertOk()->assertJson(['status' => 'pending', 'review_note' => null]);

        // Editor approves → published and live.
        Sanctum::actingAs($this->editor);
        $this->postJson("/api/posts/{$id}/approve", [], $this->h)->assertOk()->assertJson(['status' => 'published']);
        Mail::assertSent(PostWorkflowMail::class, fn ($m) => $m->event === 'published' && $m->hasTo($this->author->email));

        $this->app['auth']->forgetGuards();
        $this->getJson('/api/posts/my-story', $this->h)->assertOk();
    }

    public function test_authors_cannot_review(): void
    {
        $id = $this->draft(['status' => 'pending']);

        $this->postJson("/api/posts/{$id}/approve", [], $this->h)->assertForbidden();
        $this->postJson("/api/posts/{$id}/reject", ['note' => 'x'], $this->h)->assertForbidden();
    }

    public function test_published_posts_are_locked_for_authors(): void
    {
        $id = $this->draft(['status' => 'pending']);
        Sanctum::actingAs($this->editor);
        $this->postJson("/api/posts/{$id}/approve", [], $this->h)->assertOk();

        Sanctum::actingAs($this->author);
        $this->putJson("/api/posts/{$id}", ['title' => 'Sneaky edit'], $this->h)
            ->assertForbidden()->assertJson(['message' => 'This post is published. Only an editor can change it now.']);
        $this->deleteJson("/api/posts/{$id}", [], $this->h)->assertForbidden();

        Sanctum::actingAs($this->editor);
        $this->putJson("/api/posts/{$id}", ['title' => 'Editor fix'], $this->h)->assertOk();
    }

    public function test_scheduled_posts_go_live_on_time(): void
    {
        $id = $this->draft(['status' => 'pending']);
        Sanctum::actingAs($this->editor);

        $this->postJson("/api/posts/{$id}/approve", ['publish_at' => now()->subMinute()->toIso8601String()], $this->h)->assertUnprocessable();
        $this->postJson("/api/posts/{$id}/approve", ['publish_at' => now()->addHour()->toIso8601String()], $this->h)
            ->assertOk()->assertJson(['status' => 'scheduled']);
        Mail::assertSent(PostWorkflowMail::class, fn ($m) => $m->event === 'scheduled');

        // Hidden from readers until the time comes.
        $this->app['auth']->forgetGuards();
        $this->getJson('/api/posts?status=published', $this->h)->assertJsonCount(0, 'data');
        $this->getJson('/api/posts/my-story', $this->h)->assertNotFound();

        // An hour later it's live even before the scheduler runs…
        $this->travel(61)->minutes();
        $this->getJson('/api/posts?status=published', $this->h)->assertJsonCount(1, 'data');
        $this->getJson('/api/posts/my-story', $this->h)->assertOk();

        // …and the scheduled command records it as published.
        $this->artisan('posts:publish-scheduled')->assertSuccessful();
        $this->assertSame('published', Post::withoutGlobalScope('tenant')->find($id)->status);
    }

    public function test_editors_can_schedule_from_the_editor(): void
    {
        Sanctum::actingAs($this->editor);

        $this->postJson('/api/posts', ['title' => 'Later', 'content' => 'c', 'status' => 'scheduled'], $this->h)
            ->assertUnprocessable()->assertJsonValidationErrors('publish_at');
        $this->postJson('/api/posts', ['title' => 'Later', 'content' => 'c', 'status' => 'scheduled', 'publish_at' => now()->addDay()->toIso8601String()], $this->h)
            ->assertCreated()->assertJson(['status' => 'scheduled']);
    }

    public function test_edits_keep_revisions_that_can_be_restored(): void
    {
        $id = $this->draft();

        $this->putJson("/api/posts/{$id}", ['content' => 'v2'], $this->h)->assertOk();
        $this->putJson("/api/posts/{$id}", ['title' => 'Better title', 'content' => 'v3'], $this->h)->assertOk();
        $this->putJson("/api/posts/{$id}", ['category_id' => null], $this->h)->assertOk(); // no content change → no revision

        $revs = $this->getJson("/api/posts/{$id}/revisions", $this->h)->assertOk()->json();
        $this->assertSame(['v2', 'v1'], array_column($revs, 'content'));
        $this->assertSame('Ava Author', $revs[0]['user']['name']);

        $this->postJson("/api/posts/{$id}/revisions/{$revs[1]['id']}/restore", [], $this->h)
            ->assertOk()->assertJson(['title' => 'My story', 'content' => 'v1', 'slug' => 'my-story']);

        // The version that was replaced is kept too.
        $this->assertSame('v3', $this->getJson("/api/posts/{$id}/revisions", $this->h)->json('0.content'));
    }

    public function test_revisions_are_private_to_author_and_editors(): void
    {
        $id = $this->draft();
        $this->putJson("/api/posts/{$id}", ['content' => 'v2'], $this->h)->assertOk();

        $stranger = User::factory()->create();
        $this->acme->users()->attach($stranger->id, ['role' => 'author']);
        Sanctum::actingAs($stranger);
        $this->getJson("/api/posts/{$id}/revisions", $this->h)->assertForbidden();

        Sanctum::actingAs($this->editor);
        $this->getJson("/api/posts/{$id}/revisions", $this->h)->assertOk()->assertJsonCount(1);
    }

    public function test_workflow_emails_render(): void
    {
        $id   = $this->draft();
        $post = Post::withoutGlobalScope('tenant')->find($id);

        foreach (['submitted', 'published', 'scheduled', 'rejected'] as $event) {
            $html = (new PostWorkflowMail($post, $event, $event === 'rejected' ? 'Please add sources' : null, $this->editor))->render();
            $this->assertStringContainsString('My story', $html);
        }
        $this->assertStringContainsString('Please add sources', (new PostWorkflowMail($post, 'rejected', 'Please add sources', $this->editor))->render());
    }

    public function test_show_reports_what_the_viewer_may_do(): void
    {
        $id = $this->draft();

        $this->getJson("/api/posts/{$id}", $this->h)->assertJson(['can_edit' => true, 'can_review' => false]);
        Sanctum::actingAs($this->editor);
        $this->getJson("/api/posts/{$id}", $this->h)->assertJson(['can_edit' => true, 'can_review' => true]);
    }
}
