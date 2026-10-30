<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Post;
use App\Models\Tenant;
use App\Models\User;
use App\Support\Tenancy\CurrentTenant;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class TenancyTest extends TestCase
{
    use RefreshDatabase;

    private Tenant $main;
    private Tenant $acme;

    protected function setUp(): void
    {
        parent::setUp();

        config(['tenancy.central_domain' => 'niralagam.test']);
        $this->main = Tenant::default();
        $this->acme = Tenant::create(['name' => 'Acme', 'slug' => 'acme', 'domain' => 'blog.acme.test', 'status' => 'active']);
    }

    /** Create rows as if a request for $tenant were in flight. */
    private function inTenant(Tenant $tenant, callable $fn)
    {
        $current = app(CurrentTenant::class);
        $before  = $current->get();
        $current->set($tenant);
        try {
            return $fn();
        } finally {
            $current->set($before);
        }
    }

    private function makePost(Tenant $tenant, string $title, array $attrs = []): Post
    {
        return $this->inTenant($tenant, fn () => Post::create(array_merge([
            'user_id' => User::factory()->create()->id,
            'title'   => $title,
            'slug'    => Post::uniqueSlug($title),
            'content' => 'Body',
            'status'  => 'published',
        ], $attrs)));
    }

    public function test_migration_creates_an_active_default_tenant(): void
    {
        $this->assertNotNull($this->main);
        $this->assertSame('active', $this->main->status);
        $this->assertTrue($this->main->allowsPublicPosts());
    }

    public function test_tenant_is_resolved_from_header_subdomain_custom_domain_or_default(): void
    {
        $this->makePost($this->main, 'Main post');
        $this->makePost($this->acme, 'Acme post');

        $titles = fn ($res) => collect($res->json('data'))->pluck('title')->all();

        $this->assertSame(['Main post'], $titles($this->getJson('/api/posts')));
        $this->assertSame(['Acme post'], $titles($this->getJson('/api/posts', ['X-Tenant' => 'acme'])));
        $this->assertSame(['Acme post'], $titles($this->getJson('/api/posts', ['X-Tenant-Domain' => 'acme.niralagam.test'])));
        $this->assertSame(['Acme post'], $titles($this->getJson('/api/posts', ['X-Tenant-Domain' => 'blog.acme.test'])));
        $this->assertSame(['Acme post'], $titles($this->getJson('http://acme.niralagam.test/api/posts')));

        $this->getJson('/api/posts', ['X-Tenant' => 'nope'])->assertNotFound();
        $this->getJson('/api/posts', ['X-Tenant-Domain' => 'nope.niralagam.test'])->assertNotFound();
    }

    public function test_suspended_tenant_is_hidden_except_from_super_admins(): void
    {
        $this->acme->update(['status' => 'suspended']);

        $this->getJson('/api/posts', ['X-Tenant' => 'acme'])->assertNotFound();

        Sanctum::actingAs(User::factory()->create(['role' => 'super_admin']));
        $this->getJson('/api/posts', ['X-Tenant' => 'acme'])->assertOk();
    }

    public function test_content_from_another_tenant_cannot_be_read_or_changed(): void
    {
        $acmePost = $this->makePost($this->acme, 'Acme secret');
        $admin    = User::factory()->create(['role' => 'super_admin']);
        Sanctum::actingAs($admin);

        // Bound models are scoped: from the main site the Acme post doesn't exist.
        $this->getJson('/api/posts/'.$acmePost->slug)->assertNotFound();
        $this->putJson("/api/posts/{$acmePost->id}", ['title' => 'x'])->assertNotFound();
        $this->getJson("/api/posts/{$acmePost->id}/comments")->assertNotFound();

        $this->putJson("/api/posts/{$acmePost->id}", ['title' => 'Edited'], ['X-Tenant' => 'acme'])->assertOk();
    }

    public function test_slugs_are_unique_per_tenant_not_globally(): void
    {
        $a = $this->makePost($this->main, 'Hello World');
        $b = $this->makePost($this->acme, 'Hello World');
        $c = $this->makePost($this->acme, 'Hello World');

        $this->assertSame(['hello-world', 'hello-world', 'hello-world-2'], [$a->slug, $b->slug, $c->slug]);
    }

    public function test_new_rows_get_the_current_tenant_id(): void
    {
        $admin = User::factory()->create(['role' => 'super_admin']);
        Sanctum::actingAs($admin);

        $id = $this->postJson('/api/categories', ['name' => 'News'], ['X-Tenant' => 'acme'])->assertCreated()->json('id');
        $this->assertSame($this->acme->id, Category::withoutGlobalScope('tenant')->find($id)->tenant_id);

        $this->getJson('/api/categories')->assertJsonCount(0);
        $this->getJson('/api/categories', ['X-Tenant' => 'acme'])->assertJsonCount(1);
    }

    public function test_post_cannot_use_another_tenants_category(): void
    {
        $acmeCat = $this->inTenant($this->acme, fn () => Category::create(['name' => 'Acme', 'slug' => 'acme']));
        Sanctum::actingAs(User::factory()->create());

        $this->postJson('/api/posts', ['title' => 'T', 'content' => 'c', 'category_id' => $acmeCat->id])
            ->assertUnprocessable()->assertJsonValidationErrors('category_id');
    }

    public function test_posting_needs_an_author_role_unless_the_tenant_allows_public_posts(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);
        $body = ['title' => 'T', 'content' => 'c'];

        $this->postJson('/api/posts', $body)->assertCreated(); // main allows public posts
        $this->postJson('/api/posts', $body, ['X-Tenant' => 'acme'])->assertForbidden();

        $this->acme->users()->attach($user->id, ['role' => 'subscriber']);
        $this->postJson('/api/posts', $body, ['X-Tenant' => 'acme'])->assertForbidden();

        $this->acme->users()->updateExistingPivot($user->id, ['role' => 'author']);
        $this->postJson('/api/posts', $body, ['X-Tenant' => 'acme'])->assertCreated();
    }

    public function test_tenant_editor_manages_that_tenant_only(): void
    {
        $editor = User::factory()->create();
        $this->acme->users()->attach($editor->id, ['role' => 'editor']);
        $acmePost = $this->makePost($this->acme, 'By someone else');
        $mainPost = $this->makePost($this->main, 'Main by someone else');
        Sanctum::actingAs($editor);

        $this->postJson('/api/categories', ['name' => 'X'], ['X-Tenant' => 'acme'])->assertCreated();
        $this->putJson("/api/posts/{$acmePost->id}", ['title' => 'Edited'], ['X-Tenant' => 'acme'])->assertOk();

        $this->postJson('/api/categories', ['name' => 'X'])->assertForbidden();
        $this->putJson("/api/posts/{$mainPost->id}", ['title' => 'Edited'])->assertForbidden();
        $this->getJson('/api/admin/dashboard', ['X-Tenant' => 'acme'])->assertOk();
        $this->getJson('/api/admin/dashboard')->assertForbidden();
        $this->getJson('/api/admin/users', ['X-Tenant' => 'acme'])->assertForbidden();
    }

    public function test_drafts_are_visible_to_tenant_editors(): void
    {
        $editor = User::factory()->create();
        $this->acme->users()->attach($editor->id, ['role' => 'editor']);
        $this->makePost($this->acme, 'Draft', ['status' => 'draft']);
        Sanctum::actingAs($editor);

        $this->getJson('/api/posts?status=draft', ['X-Tenant' => 'acme'])->assertJsonCount(1, 'data');
        $this->getJson('/api/posts?status=draft')->assertJsonCount(0, 'data');
    }

    public function test_registering_joins_the_current_tenant_as_subscriber(): void
    {
        $id = $this->postJson('/api/auth/register', [
            'name' => 'Reader', 'email' => 'r@example.test', 'password' => 'password123', 'password_confirmation' => 'password123',
        ], ['X-Tenant' => 'acme'])->assertCreated()->json('user.id');

        $this->assertSame('subscriber', $this->acme->roleFor(User::find($id)));
    }

    public function test_super_admin_manages_members(): void
    {
        Sanctum::actingAs(User::factory()->create(['role' => 'super_admin']));
        $user = User::factory()->create(['email' => 'm@example.test']);

        $this->postJson("/api/admin/tenants/{$this->acme->id}/members", ['email' => 'm@example.test', 'role' => 'author'])->assertCreated();
        $this->assertSame('author', $this->acme->roleFor($user));

        $this->putJson("/api/admin/tenants/{$this->acme->id}/members/{$user->id}", ['role' => 'author'])->assertOk();
        $this->putJson("/api/admin/tenants/{$this->acme->id}/members/{$user->id}", ['role' => 'owner'])->assertUnprocessable();

        $this->deleteJson("/api/admin/tenants/{$this->acme->id}/members/{$user->id}")->assertOk();
        $this->assertNull($this->acme->roleFor($user));
    }

    public function test_tenant_delete_is_blocked_for_default_or_tenants_with_content(): void
    {
        Sanctum::actingAs(User::factory()->create(['role' => 'super_admin']));

        $this->deleteJson("/api/admin/tenants/{$this->main->id}")->assertUnprocessable();
        $this->putJson("/api/admin/tenants/{$this->main->id}", ['status' => 'suspended'])->assertUnprocessable();

        $this->makePost($this->acme, 'Keep me');
        $this->deleteJson("/api/admin/tenants/{$this->acme->id}")->assertUnprocessable();
        $this->assertNotNull($this->acme->fresh());

        $empty = Tenant::create(['name' => 'Empty', 'slug' => 'empty']);
        $this->deleteJson("/api/admin/tenants/{$empty->id}")->assertOk();
    }

    public function test_admin_tenant_list_counts_posts_across_tenants(): void
    {
        $this->makePost($this->acme, 'One');
        $this->makePost($this->acme, 'Two');
        Sanctum::actingAs(User::factory()->create(['role' => 'super_admin']));

        $row = collect($this->getJson('/api/admin/tenants')->json('data'))->firstWhere('slug', 'acme');
        $this->assertSame(2, $row['posts_count']);
    }
}
