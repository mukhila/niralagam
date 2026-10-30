<?php

namespace Tests\Feature;

use App\Mail\NewsletterConfirmMail;
use App\Mail\NewsletterDigestMail;
use App\Models\NewsletterSubscriber;
use App\Models\Post;
use App\Models\Tenant;
use App\Models\User;
use App\Support\Tenancy\CurrentTenant;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class NewsletterTest extends TestCase
{
    use RefreshDatabase;

    private Tenant $acme;
    private User $admin;
    private array $h = ['X-Tenant' => 'acme'];

    protected function setUp(): void
    {
        parent::setUp();
        Mail::fake();
        config(['tenancy.frontend_url' => 'https://niralagam.test', 'tenancy.central_domain' => 'niralagam.test']);

        $this->acme  = Tenant::create(['name' => 'Acme', 'slug' => 'acme', 'status' => 'active']);
        $this->admin = User::factory()->create();
        $this->acme->users()->attach($this->admin->id, ['role' => 'admin']);
    }

    /** Subscribe through the API and return the token from the confirmation email. */
    private function subscribe(string $email): string
    {
        $this->postJson('/api/newsletter/subscribe', ['email' => $email], $this->h)->assertOk();

        $token = null;
        Mail::assertSent(NewsletterConfirmMail::class, function ($m) use ($email, &$token) {
            if ($m->hasTo(strtolower($email))) {
                $token = basename($m->subscriber->confirmUrl());
            }
            return true;
        });

        return $token;
    }

    private function livePost(string $title, $publishedAt = null): Post
    {
        app(CurrentTenant::class)->set($this->acme);
        try {
            return Post::create([
                'user_id' => $this->admin->id, 'title' => $title, 'slug' => Post::uniqueSlug($title),
                'content' => '<p>About '.$title.'</p>', 'status' => 'published', 'published_at' => $publishedAt ?? now()->subHour(),
            ]);
        } finally {
            app(CurrentTenant::class)->set(null);
        }
    }

    public function test_double_opt_in_then_unsubscribe(): void
    {
        $token = $this->subscribe('Reader@Example.test');

        $sub = NewsletterSubscriber::withoutGlobalScope('tenant')->first();
        $this->assertSame(['reader@example.test', 'pending', $this->acme->id], [$sub->email, $sub->status, $sub->tenant_id]);
        $this->assertSame("https://acme.niralagam.test/newsletter/confirm/{$token}", $sub->confirmUrl());

        $this->postJson("/api/newsletter/confirm/{$token}")->assertOk()->assertJson(['brand' => 'Acme']);
        $this->assertSame('subscribed', $sub->fresh()->status);

        $this->postJson("/api/newsletter/unsubscribe/{$token}")->assertOk();
        $this->assertSame('unsubscribed', $sub->fresh()->status);

        $this->postJson('/api/newsletter/confirm/not-a-token')->assertNotFound();
    }

    public function test_signup_response_does_not_reveal_existing_subscribers(): void
    {
        $token = $this->subscribe('a@example.test');
        $this->postJson("/api/newsletter/confirm/{$token}");

        $first = $this->postJson('/api/newsletter/subscribe', ['email' => 'a@example.test'], $this->h)->assertOk()->json('message');
        $new   = $this->postJson('/api/newsletter/subscribe', ['email' => 'b@example.test'], $this->h)->assertOk()->json('message');

        $this->assertSame($first, $new);
        // No second confirmation for someone already subscribed.
        Mail::assertSent(NewsletterConfirmMail::class, fn ($m) => $m->hasTo('a@example.test'));
        $this->assertSame(1, Mail::sent(NewsletterConfirmMail::class, fn ($m) => $m->hasTo('a@example.test'))->count());

        $this->postJson('/api/newsletter/subscribe', ['email' => 'not-an-email'], $this->h)->assertUnprocessable();
    }

    public function test_lists_are_per_brand(): void
    {
        $this->subscribe('x@example.test');
        Tenant::create(['name' => 'Other', 'slug' => 'other', 'status' => 'active']);
        $this->postJson('/api/newsletter/subscribe', ['email' => 'x@example.test'], ['X-Tenant' => 'other'])->assertOk();

        $this->assertSame(2, NewsletterSubscriber::withoutGlobalScope('tenant')->count());

        Sanctum::actingAs($this->admin);
        $this->getJson('/api/admin/subscribers', $this->h)->assertOk()->assertJsonCount(1, 'data')->assertJsonPath('counts.pending', 1);
        $this->getJson('/api/admin/subscribers', ['X-Tenant' => 'other'])->assertForbidden();
    }

    public function test_digest_goes_only_to_confirmed_subscribers_with_new_posts(): void
    {
        $confirmed = $this->subscribe('yes@example.test');
        $this->postJson("/api/newsletter/confirm/{$confirmed}");
        $this->subscribe('pending@example.test');
        $gone = $this->subscribe('gone@example.test');
        $this->postJson("/api/newsletter/confirm/{$gone}");
        $this->postJson("/api/newsletter/unsubscribe/{$gone}");

        $this->livePost('Old news', now()->subDays(10));
        $this->livePost('Fresh story');

        Sanctum::actingAs($this->admin);
        $this->getJson('/api/admin/newsletter/digest', $this->h)->assertOk()
            ->assertJson(['digest_enabled' => true, 'recipients' => 1])
            ->assertJsonCount(1, 'posts')->assertJsonPath('posts.0.title', 'Fresh story');

        $this->postJson('/api/admin/newsletter/digest/send', [], $this->h)->assertOk()->assertJson(['sent' => 1]);

        Mail::assertQueued(NewsletterDigestMail::class, 1);
        Mail::assertQueued(NewsletterDigestMail::class, fn ($m) => $m->hasTo('yes@example.test')
            && $m->posts->pluck('title')->all() === ['Fresh story']);

        // The next digest starts from now, so the same posts aren't sent twice.
        $this->postJson('/api/admin/newsletter/digest/send', [], $this->h)->assertUnprocessable();
    }

    public function test_digest_email_has_unsubscribe_link_and_header(): void
    {
        $token = $this->subscribe('r@example.test');
        $this->postJson("/api/newsletter/confirm/{$token}");
        $sub  = NewsletterSubscriber::withoutGlobalScope('tenant')->first();
        $post = $this->livePost('Hello & welcome');

        $mail = new NewsletterDigestMail($sub, $this->acme, collect([$post]));
        $html = $mail->render();

        $this->assertStringContainsString("https://acme.niralagam.test/newsletter/unsubscribe/{$token}", $html);
        $this->assertStringContainsString('https://acme.niralagam.test/blog/hello-welcome', $html);
        $this->assertStringContainsString('Hello &amp; welcome', $html);
        $this->assertSame("<https://acme.niralagam.test/newsletter/unsubscribe/{$token}>", $mail->headers()->text['List-Unsubscribe']);
    }

    public function test_weekly_command_respects_the_switch(): void
    {
        $token = $this->subscribe('w@example.test');
        $this->postJson("/api/newsletter/confirm/{$token}");
        $this->livePost('Weekly item');

        Sanctum::actingAs($this->admin);
        $this->putJson('/api/admin/newsletter/digest', ['digest_enabled' => false], $this->h)->assertOk()->assertJson(['digest_enabled' => false]);

        $this->artisan('newsletter:send-digest')->assertSuccessful();
        Mail::assertNothingQueued();

        $this->putJson('/api/admin/newsletter/digest', ['digest_enabled' => true], $this->h)->assertOk();
        $this->artisan('newsletter:send-digest')->expectsOutputToContain('acme: sent to 1')->assertSuccessful();
        Mail::assertQueued(NewsletterDigestMail::class, 1);
    }

    public function test_export_and_remove_and_permissions(): void
    {
        $token = $this->subscribe('csv@example.test');
        $this->postJson("/api/newsletter/confirm/{$token}");

        Sanctum::actingAs($this->admin);
        $csv = $this->get('/api/admin/subscribers/export', $this->h)->assertOk()->streamedContent();
        $this->assertStringContainsString("email,confirmed_at,last_sent_at\ncsv@example.test,", $csv);

        $id = $this->getJson('/api/admin/subscribers', $this->h)->json('data.0.id');
        $this->deleteJson("/api/admin/subscribers/{$id}", [], $this->h)->assertOk();
        $this->assertSame(0, NewsletterSubscriber::withoutGlobalScope('tenant')->count());

        $editor = User::factory()->create();
        $this->acme->users()->attach($editor->id, ['role' => 'editor']);
        Sanctum::actingAs($editor);
        $this->getJson('/api/admin/subscribers', $this->h)->assertForbidden();
        $this->postJson('/api/admin/newsletter/digest/send', [], $this->h)->assertForbidden();
    }
}
