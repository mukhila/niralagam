<?php

namespace Tests\Feature;

use App\Mail\TenantInvitationMail;
use App\Models\Tenant;
use App\Models\TenantInvitation;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class InvitationTest extends TestCase
{
    use RefreshDatabase;

    private Tenant $acme;
    private User $admin;
    private array $acmeHeader = ['X-Tenant' => 'acme'];

    protected function setUp(): void
    {
        parent::setUp();
        Mail::fake();

        $this->acme  = Tenant::create(['name' => 'Acme', 'slug' => 'acme', 'status' => 'active']);
        $this->admin = User::factory()->create(['name' => 'Ann Admin']);
        $this->acme->users()->attach($this->admin->id, ['role' => 'admin']);
    }

    /** Sends an invitation as the brand admin; returns [response, token]. */
    private function invite(string $email, string $role = 'author'): array
    {
        Sanctum::actingAs($this->admin);
        $res   = $this->postJson('/api/admin/invitations', ['email' => $email, 'role' => $role], $this->acmeHeader)->assertCreated();
        $token = basename($res->json('invite_url'));

        return [$res, $token];
    }

    public function test_admin_sends_an_invitation_email_with_a_working_link(): void
    {
        [$res, $token] = $this->invite('New.Person@Example.test', 'editor');

        $res->assertJson(['email' => 'new.person@example.test', 'role' => 'editor', 'email_sent' => true]);
        $this->assertStringStartsWith(config('tenancy.frontend_url').'/invite/', $res->json('invite_url'));

        Mail::assertSent(TenantInvitationMail::class, fn ($mail) =>
            $mail->hasTo('new.person@example.test') && $mail->url === $res->json('invite_url'));

        // Only a hash is stored, never the token itself.
        $row = TenantInvitation::first();
        $this->assertNotSame($token, $row->token_hash);
        $this->assertSame(hash('sha256', $token), $row->token_hash);
        $this->assertArrayNotHasKey('token_hash', $res->json());
    }

    public function test_invitation_email_renders(): void
    {
        [, $token] = $this->invite('r@example.test');
        $inv = TenantInvitation::findByToken($token)->load('tenant', 'inviter');

        $html = (new TenantInvitationMail($inv, TenantInvitation::url($token)))->render();
        $this->assertStringContainsString('Ann Admin invited you to join', preg_replace('/\s+/', ' ', $html));
        $this->assertStringContainsString('Acme', $html);
        $this->assertStringContainsString('/invite/'.$token, $html);
    }

    public function test_duplicate_or_member_invites_are_rejected(): void
    {
        $this->invite('dup@example.test');
        $this->postJson('/api/admin/invitations', ['email' => 'DUP@example.test', 'role' => 'author'], $this->acmeHeader)
            ->assertUnprocessable();

        $member = User::factory()->create(['email' => 'member@example.test']);
        $this->acme->users()->attach($member->id, ['role' => 'author']);
        $this->postJson('/api/admin/invitations', ['email' => 'member@example.test', 'role' => 'editor'], $this->acmeHeader)
            ->assertUnprocessable();
    }

    public function test_only_brand_admins_can_invite(): void
    {
        $editor = User::factory()->create();
        $this->acme->users()->attach($editor->id, ['role' => 'editor']);
        Sanctum::actingAs($editor);

        $this->postJson('/api/admin/invitations', ['email' => 'x@example.test', 'role' => 'admin'], $this->acmeHeader)->assertForbidden();
        $this->getJson('/api/admin/invitations', $this->acmeHeader)->assertForbidden();
    }

    public function test_invitee_sees_details_then_accepts_after_signing_up(): void
    {
        [, $token] = $this->invite('fresh@example.test', 'author');

        $this->getJson("/api/invitations/{$token}")->assertOk()->assertJson([
            'brand' => ['name' => 'Acme'], 'email' => 'fresh@example.test', 'role' => 'author',
            'invited_by' => 'Ann Admin', 'has_account' => false,
        ]);

        $this->app['auth']->forgetGuards();
        $userToken = $this->postJson('/api/auth/register', [
            'name' => 'Fresh', 'email' => 'Fresh@example.test', 'password' => 'password123', 'password_confirmation' => 'password123',
        ])->assertCreated()->json('token');

        $this->postJson("/api/invitations/{$token}/accept", [], ['Authorization' => "Bearer {$userToken}"])
            ->assertOk()->assertJson(['role' => 'author', 'brand' => ['slug' => 'acme']]);

        $user = User::where('email', 'Fresh@example.test')->first();
        $this->assertSame('author', $this->acme->roleFor($user));
        $this->assertNotNull(TenantInvitation::first()->accepted_at);

        // One use only.
        $this->getJson("/api/invitations/{$token}")->assertStatus(410);
        $this->postJson("/api/invitations/{$token}/accept", [], ['Authorization' => "Bearer {$userToken}"])->assertStatus(410);
    }

    public function test_accepting_requires_sign_in_and_the_invited_email(): void
    {
        [, $token] = $this->invite('right@example.test');

        $this->app['auth']->forgetGuards();
        $this->postJson("/api/invitations/{$token}/accept")->assertUnauthorized();

        Sanctum::actingAs(User::factory()->create(['email' => 'wrong@example.test']));
        $this->postJson("/api/invitations/{$token}/accept")->assertForbidden()
            ->assertJsonFragment(['message' => 'This invitation was sent to right@example.test. Sign in with that email to accept it.']);
        $this->assertNull(TenantInvitation::first()->accepted_at);
    }

    public function test_expired_revoked_and_unknown_links_fail(): void
    {
        [, $expired] = $this->invite('old@example.test');
        TenantInvitation::findByToken($expired)->update(['expires_at' => now()->subMinute()]);
        $this->getJson("/api/invitations/{$expired}")->assertStatus(410);

        [$res, $revoked] = $this->invite('gone@example.test');
        $this->deleteJson("/api/admin/invitations/{$res->json('id')}", [], $this->acmeHeader)->assertOk();
        $this->getJson("/api/invitations/{$revoked}")->assertNotFound();

        $this->getJson('/api/invitations/not-a-real-token')->assertNotFound();
    }

    public function test_resend_replaces_the_link_and_extends_expiry(): void
    {
        [$res, $old] = $this->invite('again@example.test');
        TenantInvitation::find($res->json('id'))->update(['expires_at' => now()->subDay()]);

        $new = basename($this->postJson("/api/admin/invitations/{$res->json('id')}/resend", [], $this->acmeHeader)
            ->assertOk()->json('invite_url'));

        $this->assertNotSame($old, $new);
        $this->getJson("/api/invitations/{$old}")->assertNotFound();
        $this->getJson("/api/invitations/{$new}")->assertOk();
        Mail::assertSent(TenantInvitationMail::class, 2);
    }

    public function test_accepting_never_downgrades_an_existing_member(): void
    {
        $editor = User::factory()->create(['email' => 'ed@example.test']);
        $this->acme->users()->attach($editor->id, ['role' => 'editor']);
        // Invite created before they became an editor, e.g. as subscriber.
        $inv   = new TenantInvitation(['tenant_id' => $this->acme->id, 'email' => 'ed@example.test', 'role' => 'subscriber']);
        $token = $inv->refreshToken();
        $inv->save();

        Sanctum::actingAs($editor);
        $this->postJson("/api/invitations/{$token}/accept")->assertOk()->assertJson(['role' => 'editor']);
        $this->assertSame('editor', $this->acme->roleFor($editor));
    }

    public function test_admins_cannot_touch_another_brands_invitations(): void
    {
        $other = Tenant::create(['name' => 'Other', 'slug' => 'other', 'status' => 'active']);
        $inv   = new TenantInvitation(['tenant_id' => $other->id, 'email' => 'o@example.test', 'role' => 'author']);
        $inv->refreshToken();
        $inv->save();

        Sanctum::actingAs($this->admin);
        $this->getJson('/api/admin/invitations', $this->acmeHeader)->assertOk()->assertJsonCount(0);
        $this->deleteJson("/api/admin/invitations/{$inv->id}", [], $this->acmeHeader)->assertNotFound();
        $this->postJson("/api/admin/invitations/{$inv->id}/resend", [], $this->acmeHeader)->assertNotFound();
    }

    public function test_failed_email_still_returns_a_shareable_link(): void
    {
        Mail::shouldReceive('to')->andThrow(new \RuntimeException('SMTP down'));
        Sanctum::actingAs($this->admin);

        $this->postJson('/api/admin/invitations', ['email' => 'x@example.test', 'role' => 'author'], $this->acmeHeader)
            ->assertCreated()->assertJson(['email_sent' => false])->assertJsonStructure(['invite_url']);
    }
}
