<?php

namespace Tests\Feature;

use App\Models\AuditLog;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class AuditLogTest extends TestCase
{
    use RefreshDatabase;

    private Tenant $acme;
    private User $admin;
    private User $author;
    private array $h = ['X-Tenant' => 'acme'];

    protected function setUp(): void
    {
        parent::setUp();
        Mail::fake();
        $this->acme   = Tenant::create(['name' => 'Acme', 'slug' => 'acme', 'status' => 'active']);
        $this->admin  = User::factory()->create(['name' => 'Ann Admin']);
        $this->author = User::factory()->create(['name' => 'Ava Author']);
        $this->acme->users()->attach($this->admin->id, ['role' => 'admin']);
        $this->acme->users()->attach($this->author->id, ['role' => 'author']);
    }

    private function actions(): array
    {
        return AuditLog::orderBy('id')->pluck('action')->all();
    }

    public function test_editorial_actions_are_recorded_with_actor_and_changes(): void
    {
        Sanctum::actingAs($this->author);
        $id = $this->postJson('/api/posts', ['title' => 'Rain', 'content' => '<p>v1</p>'], $this->h)->assertCreated()->json('id');
        $this->putJson("/api/posts/{$id}", ['title' => 'Monsoon rain', 'content' => '<p>v2</p>'], $this->h)->assertOk();
        $this->postJson("/api/posts/{$id}/submit", [], $this->h)->assertOk();

        Sanctum::actingAs($this->admin);
        $this->postJson("/api/posts/{$id}/reject", ['note' => 'Add a photo'], $this->h)->assertOk();

        $this->assertSame(['post.created', 'post.updated', 'post.submitted', 'post.rejected'], $this->actions());

        $edit = AuditLog::where('action', 'post.updated')->first();
        $this->assertSame([$this->acme->id, $this->author->id, 'Ava Author', 'post', $id, 'Monsoon rain'],
            [$edit->tenant_id, $edit->user_id, $edit->actor_name, $edit->subject_type, $edit->subject_id, $edit->subject_label]);
        $this->assertSame(['Rain', 'Monsoon rain'], $edit->changes['title']);
        $this->assertSame(['(changed)', '(changed)'], $edit->changes['content']); // body text isn't copied into the log

        $this->assertSame([null, 'Add a photo'], AuditLog::where('action', 'post.rejected')->first()->changes['note']);
    }

    public function test_membership_and_settings_changes_are_recorded(): void
    {
        Sanctum::actingAs($this->admin);
        $this->putJson("/api/admin/members/{$this->author->id}", ['role' => 'editor'], $this->h)->assertOk();
        $this->postJson('/api/admin/invitations', ['email' => 'new@example.test', 'role' => 'author'], $this->h)->assertCreated();
        $this->putJson('/api/admin/brand', ['tagline' => 'Coastal news', 'name' => 'Acme Daily'], $this->h)->assertOk();
        $this->get('/api/admin/subscribers/export', $this->h)->assertOk();

        $this->assertSame(['member.role_changed', 'invitation.sent', 'brand.settings_updated', 'subscribers.exported'], $this->actions());
        $this->assertSame(['author', 'editor'], AuditLog::where('action', 'member.role_changed')->first()->changes['role']);
        $this->assertSame('new@example.test', AuditLog::where('action', 'invitation.sent')->first()->subject_label);
        $settings = AuditLog::where('action', 'brand.settings_updated')->first()->changes;
        $this->assertSame(['Acme', 'Acme Daily'], $settings['name']);
        $this->assertEqualsCanonicalizing(['tagline', 'name'], $settings['fields'][1]);
    }

    public function test_brand_admins_see_only_their_brand_and_can_filter(): void
    {
        Sanctum::actingAs($this->admin);
        $this->postJson('/api/categories', ['name' => 'News'], $this->h)->assertCreated();
        $this->putJson("/api/admin/members/{$this->author->id}", ['role' => 'editor'], $this->h)->assertOk();

        $other = Tenant::create(['name' => 'Other', 'slug' => 'other', 'status' => 'active']);
        AuditLog::create(['tenant_id' => $other->id, 'action' => 'post.created', 'subject_label' => 'Secret']);
        AuditLog::create(['tenant_id' => null, 'action' => 'tenant.created', 'subject_label' => 'Platform thing']);

        $res = $this->getJson('/api/admin/audit', $this->h)->assertOk();
        $this->assertSame(['member.role_changed', 'category.created'], array_column($res->json('data'), 'action'));
        $this->assertSame([['id' => $this->admin->id, 'name' => 'Ann Admin']], $res->json('actors'));

        $this->getJson('/api/admin/audit?group=member', $this->h)->assertJsonCount(1, 'data');
        $this->getJson('/api/admin/audit?search=News', $this->h)->assertJsonCount(1, 'data');
        $this->getJson('/api/admin/audit/platform', $this->h)->assertForbidden();
    }

    public function test_only_brand_admins_and_super_admins_can_read_it(): void
    {
        Sanctum::actingAs($this->author);
        $this->getJson('/api/admin/audit', $this->h)->assertForbidden();

        $super = User::factory()->create(['role' => 'super_admin']);
        Sanctum::actingAs($super);
        $this->postJson('/api/admin/tenants', ['name' => 'Fresh'])->assertCreated();
        $this->putJson("/api/admin/users/{$this->author->id}", ['status' => 'suspended'])->assertOk();

        $platform = $this->getJson('/api/admin/audit/platform?tenant=platform')->assertOk();
        $this->assertSame(['user.updated', 'tenant.created'], array_column($platform->json('data'), 'action'));
        $this->assertSame(['active', 'suspended'], $platform->json('data.0.changes.status'));
    }

    public function test_entries_cannot_be_changed_through_the_api_and_old_ones_are_pruned(): void
    {
        AuditLog::create(['tenant_id' => $this->acme->id, 'action' => 'post.created'])->forceFill(['created_at' => now()->subDays(400)])->save();
        AuditLog::create(['tenant_id' => $this->acme->id, 'action' => 'post.updated']);

        Sanctum::actingAs($this->admin);
        $this->deleteJson('/api/admin/audit', [], $this->h)->assertStatus(405);

        $this->artisan('audit:prune')->expectsOutputToContain('Pruned 1')->assertSuccessful();
        $this->assertSame(['post.updated'], $this->actions());
    }
}
