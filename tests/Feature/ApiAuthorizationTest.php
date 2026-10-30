<?php

namespace Tests\Feature;

use App\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class ApiAuthorizationTest extends TestCase
{
    use RefreshDatabase;

    private function makePost(User $owner, array $attrs = []): Post
    {
        return Post::create(array_merge([
            'user_id' => $owner->id,
            'title'   => 'Hello',
            'slug'    => 'hello-'.uniqid(),
            'content' => 'Body',
            'status'  => 'published',
        ], $attrs));
    }

    public function test_regular_user_cannot_reach_admin_endpoints(): void
    {
        $user  = User::factory()->create(['role' => 'user']);
        $other = User::factory()->create(['role' => 'user']);
        Sanctum::actingAs($user);

        $this->getJson('/api/admin/users')->assertForbidden();
        $this->getJson('/api/admin/dashboard')->assertForbidden();
        $this->getJson('/api/admin/tenants')->assertForbidden();
        $this->postJson('/api/admin/tenants', ['name' => 'X'])->assertForbidden();
        $this->postJson('/api/categories', ['name' => 'X'])->assertForbidden();
        $this->postJson('/api/tags', ['name' => 'X'])->assertForbidden();
        $this->putJson("/api/admin/users/{$user->id}", ['role' => 'super_admin'])->assertForbidden();
        $this->putJson("/api/admin/users/{$other->id}", ['role' => 'super_admin'])->assertForbidden();

        $this->assertSame('user', $user->fresh()->role);
        $this->assertSame('user', $other->fresh()->role);
    }

    public function test_super_admin_can_manage_users_but_not_themselves(): void
    {
        $admin = User::factory()->create(['role' => 'super_admin']);
        $user  = User::factory()->create(['role' => 'user']);
        $user->createToken('t');
        Sanctum::actingAs($admin);

        $this->getJson('/api/admin/users')->assertOk();
        $this->putJson("/api/admin/users/{$user->id}", ['status' => 'suspended'])->assertOk();
        $this->assertSame('suspended', $user->fresh()->status);
        $this->assertSame(0, $user->tokens()->count());

        $this->putJson("/api/admin/users/{$user->id}", ['role' => 'admin'])->assertUnprocessable();
        $this->putJson("/api/admin/users/{$admin->id}", ['role' => 'user'])->assertUnprocessable();
        $this->assertSame('super_admin', $admin->fresh()->role);
    }

    public function test_suspended_user_cannot_log_in(): void
    {
        User::factory()->create(['email' => 'a@example.test', 'password' => 'password123', 'status' => 'suspended']);

        $this->postJson('/api/auth/login', ['email' => 'a@example.test', 'password' => 'password123'])
            ->assertUnprocessable();
    }

    public function test_api_login_returns_a_token_without_starting_a_session(): void
    {
        User::factory()->create(['email' => 'a@example.test', 'password' => 'password123']);

        $this->postJson('/api/auth/login', ['email' => 'a@example.test', 'password' => 'password123'])
            ->assertOk()->assertJsonStructure(['token']);
        $this->assertGuest('web');

        $this->postJson('/api/auth/login', ['email' => 'a@example.test', 'password' => 'wrong'])->assertUnprocessable();
    }

    public function test_user_cannot_edit_or_delete_someone_elses_post(): void
    {
        $owner    = User::factory()->create();
        $attacker = User::factory()->create();
        $post     = $this->makePost($owner);
        Sanctum::actingAs($attacker);

        $this->putJson("/api/posts/{$post->id}", ['title' => 'Hacked'])->assertForbidden();
        $this->deleteJson("/api/posts/{$post->id}")->assertForbidden();
        $this->assertSame('Hello', $post->fresh()->title);
    }

    public function test_owner_and_super_admin_can_edit_a_post(): void
    {
        $owner = User::factory()->create();
        $post  = $this->makePost($owner, ['status' => 'draft']);

        Sanctum::actingAs($owner);
        $this->putJson("/api/posts/{$post->id}", ['title' => 'Mine'])->assertOk();

        // Once published, only an editor can change it (editorial workflow).
        $post->update(['status' => 'published']);
        $this->putJson("/api/posts/{$post->id}", ['title' => 'Again'])->assertForbidden();

        Sanctum::actingAs(User::factory()->create(['role' => 'super_admin']));
        $this->deleteJson("/api/posts/{$post->id}")->assertOk();
    }

    public function test_duplicate_titles_get_unique_slugs(): void
    {
        Sanctum::actingAs(User::factory()->create());

        $a = $this->postJson('/api/posts', ['title' => 'Same Title', 'content' => 'x'])->assertCreated()->json('slug');
        $b = $this->postJson('/api/posts', ['title' => 'Same Title', 'content' => 'x'])->assertCreated()->json('slug');
        $id = $this->postJson('/api/posts', ['title' => 'Other', 'content' => 'x'])->json('id');
        $c = $this->putJson("/api/posts/{$id}", ['title' => 'Same Title'])->assertOk()->json('slug');

        $this->assertSame(['same-title', 'same-title-2', 'same-title-3'], [$a, $b, $c]);
    }

    public function test_drafts_are_hidden_from_other_users(): void
    {
        $owner = User::factory()->create();
        $draft = $this->makePost($owner, ['status' => 'draft', 'slug' => 'secret-draft']);

        $this->getJson('/api/posts/secret-draft')->assertNotFound();
        $this->getJson('/api/posts')->assertJsonCount(0, 'data');

        Sanctum::actingAs(User::factory()->create());
        $this->getJson('/api/posts/secret-draft')->assertNotFound();
        $this->getJson('/api/posts?status=draft')->assertJsonCount(0, 'data');

        Sanctum::actingAs($owner);
        $this->getJson('/api/posts/secret-draft')->assertOk();
        $this->getJson('/api/posts?status=draft')->assertJsonCount(1, 'data');
    }

    public function test_comment_can_be_marked_rejected(): void
    {
        $post = $this->makePost(User::factory()->create());
        $comment = $post->comments()->create(['user_id' => $post->user_id, 'content' => 'hi', 'status' => 'approved']);
        Sanctum::actingAs(User::factory()->create(['role' => 'super_admin']));

        $this->putJson("/api/admin/comments/{$comment->id}/status", ['status' => 'rejected'])->assertOk();
        $this->assertSame('rejected', $comment->fresh()->status);
    }
}
