<?php

namespace Tests\Feature;

use App\Models\Comment;
use App\Models\Post;
use App\Models\PostView;
use App\Models\Tenant;
use App\Models\User;
use App\Support\Tenancy\CurrentTenant;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class BrandAdminTest extends TestCase
{
    use RefreshDatabase;

    private Tenant $acme;
    private Tenant $other;
    private User $admin;
    private User $editor;

    private array $acmeHeader = ['X-Tenant' => 'acme'];

    protected function setUp(): void
    {
        parent::setUp();

        $this->acme  = Tenant::create(['name' => 'Acme', 'slug' => 'acme', 'status' => 'active']);
        $this->other = Tenant::create(['name' => 'Other', 'slug' => 'other', 'status' => 'active']);

        $this->admin  = User::factory()->create(['name' => 'Ann Admin']);
        $this->editor = User::factory()->create(['name' => 'Ed Editor']);
        $this->acme->users()->attach($this->admin->id, ['role' => 'admin']);
        $this->acme->users()->attach($this->editor->id, ['role' => 'editor']);
    }

    private function inTenant(Tenant $tenant, callable $fn)
    {
        $current = app(CurrentTenant::class);
        $current->set($tenant);
        try {
            return $fn();
        } finally {
            $current->set(null);
        }
    }

    public function test_brands_endpoint_lists_memberships_or_everything_for_super_admin(): void
    {
        $this->other->users()->attach($this->admin->id, ['role' => 'subscriber']);

        Sanctum::actingAs($this->admin);
        $brands = collect($this->getJson('/api/auth/brands')->assertOk()->json());
        $this->assertSame(['acme' => 'admin', 'other' => 'subscriber'], $brands->pluck('role', 'slug')->all());

        Sanctum::actingAs(User::factory()->create(['role' => 'super_admin']));
        $this->assertCount(3, $this->getJson('/api/auth/brands')->json()); // main, acme, other
    }

    public function test_brand_staff_get_a_brand_scoped_dashboard(): void
    {
        $this->inTenant($this->acme, function () {
            $post = Post::create(['user_id' => $this->editor->id, 'title' => 'A', 'slug' => 'a', 'content' => 'c', 'status' => 'published']);
            PostView::create(['post_id' => $post->id, 'ip_address' => '1.1.1.1']);
        });
        $this->inTenant($this->other, fn () =>
            Post::create(['user_id' => $this->editor->id, 'title' => 'B', 'slug' => 'b', 'content' => 'c', 'status' => 'published']));

        Sanctum::actingAs($this->editor);
        $res = $this->getJson('/api/admin/dashboard?range=7d', $this->acmeHeader)->assertOk();

        $this->assertSame('brand', $res->json('scope'));
        $this->assertSame(1, $res->json('stats.posts_published'));
        $this->assertSame(1, $res->json('stats.views'));
        $this->assertSame(2, $res->json('stats.users'));     // Acme's members, not all accounts
        $this->assertSame(0, $res->json('stats.tenants_total'));
        $this->assertSame([], $res->json('tenants'));
        $this->assertEqualsCanonicalizing(['Ann Admin', 'Ed Editor'], collect($res->json('recentUsers'))->pluck('name')->all());

        // Not a member of Other, and authors/subscribers can't open the panel.
        $this->getJson('/api/admin/dashboard', ['X-Tenant' => 'other'])->assertForbidden();
        $author = User::factory()->create();
        $this->acme->users()->attach($author->id, ['role' => 'author']);
        Sanctum::actingAs($author);
        $this->getJson('/api/admin/dashboard', $this->acmeHeader)->assertForbidden();
    }

    public function test_super_admin_dashboard_is_platform_wide(): void
    {
        Sanctum::actingAs(User::factory()->create(['role' => 'super_admin']));
        $res = $this->getJson('/api/admin/dashboard?range=30d')->assertOk();

        $this->assertSame('platform', $res->json('scope'));
        $this->assertSame(3, $res->json('stats.tenants_total'));
    }

    public function test_editors_moderate_comments_in_their_brand(): void
    {
        $comment = $this->inTenant($this->acme, function () {
            $post = Post::create(['user_id' => $this->admin->id, 'title' => 'A', 'slug' => 'a', 'content' => 'c', 'status' => 'published']);
            return Comment::create(['post_id' => $post->id, 'user_id' => $this->admin->id, 'content' => 'spam?', 'status' => 'approved']);
        });

        Sanctum::actingAs($this->editor);
        $this->getJson('/api/admin/comments', $this->acmeHeader)->assertOk()->assertJsonCount(1, 'data');
        $this->getJson('/api/admin/comments')->assertForbidden(); // default brand: not a member
        $this->putJson("/api/admin/comments/{$comment->id}/status", ['status' => 'spam'], $this->acmeHeader)->assertOk();
        $this->deleteJson("/api/comments/{$comment->id}", [], $this->acmeHeader)->assertOk();
    }

    public function test_platform_pages_stay_super_admin_only(): void
    {
        Sanctum::actingAs($this->admin);

        $this->getJson('/api/admin/users', $this->acmeHeader)->assertForbidden();
        $this->getJson('/api/admin/tenants', $this->acmeHeader)->assertForbidden();
    }

    public function test_brand_admin_manages_members(): void
    {
        // Members join via invitations (InvitationTest); attach directly here.
        $reader = User::factory()->create(['email' => 'reader@example.test']);
        $this->acme->users()->attach($reader->id, ['role' => 'author']);
        Sanctum::actingAs($this->admin);

        // Direct adding was replaced by invitations.
        $this->postJson('/api/admin/members', ['email' => 'x@example.test', 'role' => 'author'], $this->acmeHeader)
            ->assertStatus(405);

        $list = $this->getJson('/api/admin/members?role=author', $this->acmeHeader)->assertOk();
        $this->assertSame(['reader@example.test'], collect($list->json('data'))->pluck('email')->all());

        $this->putJson("/api/admin/members/{$reader->id}", ['role' => 'editor'], $this->acmeHeader)->assertOk();
        $this->assertSame('editor', $this->acme->roleFor($reader));

        $this->deleteJson("/api/admin/members/{$reader->id}", [], $this->acmeHeader)->assertOk();
        $this->assertNull($this->acme->roleFor($reader));

        // Another brand's members are out of reach.
        $this->getJson('/api/admin/members', ['X-Tenant' => 'other'])->assertForbidden();
    }

    public function test_editors_cannot_manage_members(): void
    {
        Sanctum::actingAs($this->editor);

        $this->getJson('/api/admin/members', $this->acmeHeader)->assertForbidden();
        $this->putJson("/api/admin/members/{$this->admin->id}", ['role' => 'subscriber'], $this->acmeHeader)->assertForbidden();
    }

    public function test_brand_admin_cannot_lock_themselves_or_the_brand_out(): void
    {
        Sanctum::actingAs($this->admin);

        $this->putJson("/api/admin/members/{$this->admin->id}", ['role' => 'editor'], $this->acmeHeader)->assertUnprocessable();
        $this->deleteJson("/api/admin/members/{$this->admin->id}", [], $this->acmeHeader)->assertUnprocessable();
        $this->assertSame('admin', $this->acme->roleFor($this->admin));

        // Even a super admin can't remove the last admin.
        Sanctum::actingAs(User::factory()->create(['role' => 'super_admin']));
        $this->deleteJson("/api/admin/members/{$this->admin->id}", [], $this->acmeHeader)->assertUnprocessable();

        $this->putJson("/api/admin/members/{$this->editor->id}", ['role' => 'admin'], $this->acmeHeader)->assertOk();
        $this->deleteJson("/api/admin/members/{$this->admin->id}", [], $this->acmeHeader)->assertOk();
    }

    public function test_member_changes_on_a_non_member_404(): void
    {
        $stranger = User::factory()->create();
        Sanctum::actingAs($this->admin);

        $this->putJson("/api/admin/members/{$stranger->id}", ['role' => 'author'], $this->acmeHeader)->assertNotFound();
    }

    public function test_admin_editor_can_load_a_post_by_id(): void
    {
        $post = $this->inTenant($this->acme, fn () =>
            Post::create(['user_id' => $this->admin->id, 'title' => 'A', 'slug' => 'a-post', 'content' => 'c', 'status' => 'draft']));

        Sanctum::actingAs($this->editor);
        $this->getJson("/api/posts/{$post->id}", $this->acmeHeader)->assertOk()->assertJson(['slug' => 'a-post']);
        $this->getJson('/api/posts/a-post', $this->acmeHeader)->assertOk();
    }
}
