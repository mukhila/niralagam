<?php

namespace Tests\Feature;

use App\Models\NewsletterSubscriber;
use App\Models\Post;
use App\Models\PostView;
use App\Models\Tenant;
use App\Models\User;
use App\Support\Analytics\Traffic;
use App\Support\Tenancy\CurrentTenant;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class AnalyticsTest extends TestCase
{
    use RefreshDatabase;

    private const BROWSER = 'Mozilla/5.0 (Windows NT 10.0) Chrome/130.0';
    private const PHONE   = 'Mozilla/5.0 (iPhone; CPU iPhone OS 17_0) Mobile/15E148';

    private Tenant $acme;
    private User $ava;
    private User $ben;
    private User $editor;
    private array $h = ['X-Tenant' => 'acme'];

    protected function setUp(): void
    {
        parent::setUp();
        $this->acme   = Tenant::create(['name' => 'Acme', 'slug' => 'acme', 'status' => 'active']);
        $this->ava    = User::factory()->create(['name' => 'Ava', 'username' => 'ava']);
        $this->ben    = User::factory()->create(['name' => 'Ben', 'username' => 'ben']);
        $this->editor = User::factory()->create();
        $this->acme->users()->attach($this->editor->id, ['role' => 'editor']);
    }

    private function inAcme(callable $fn)
    {
        app(CurrentTenant::class)->set($this->acme);
        try {
            return $fn();
        } finally {
            app(CurrentTenant::class)->set(null);
        }
    }

    private function makePost(User $by, string $title, string $status = 'published'): Post
    {
        return $this->inAcme(fn () => Post::create([
            'user_id' => $by->id, 'title' => $title, 'slug' => Post::uniqueSlug($title), 'content' => '<p>x</p>',
            'status' => $status, 'published_at' => $status === 'published' ? now()->subDays(2) : null,
        ]));
    }

    private function recordView(Post $post, string $ip, array $body = [], string $agent = self::BROWSER)
    {
        return $this->withServerVariables(['REMOTE_ADDR' => $ip])
            ->postJson("/api/posts/{$post->id}/view", $body, $this->h + ['User-Agent' => $agent]);
    }

    public function test_traffic_classification(): void
    {
        $this->assertSame(['search', 'google.com'], Traffic::source('https://www.google.com/search?q=x', null, 'acme.test'));
        $this->assertSame(['social', 'l.facebook.com'], Traffic::source('https://l.facebook.com/l.php?u=x', null, 'acme.test'));
        $this->assertSame(['social', 't.co'], Traffic::source('https://t.co/abc', null, 'acme.test'));
        $this->assertSame(['referral', 'someblog.test'], Traffic::source('https://someblog.test/post', null, 'acme.test'));
        $this->assertSame(['newsletter', null], Traffic::source('https://mail.google.com/', 'newsletter', 'acme.test'));
        $this->assertSame(['newsletter', null], Traffic::source(null, 'follow-alert', 'acme.test'));
        $this->assertSame(['direct', null], Traffic::source(null, null, 'acme.test'));
        $this->assertSame(['direct', null], Traffic::source('https://acme.test/blog', null, 'acme.test'));

        $this->assertSame('mobile', Traffic::device(self::PHONE));
        $this->assertSame('tablet', Traffic::device('Mozilla/5.0 (iPad; CPU OS 17_0)'));
        $this->assertSame('desktop', Traffic::device(self::BROWSER));
        $this->assertTrue(Traffic::isBot('Mozilla/5.0 (compatible; Googlebot/2.1)'));
        $this->assertTrue(Traffic::isBot(null));
    }

    public function test_views_are_recorded_with_source_and_device_once_per_reader(): void
    {
        $post = $this->makePost($this->ava, 'Rain');

        $this->recordView($post, '1.1.1.1', ['referrer' => 'https://www.google.com/'], self::PHONE)->assertOk();
        $this->recordView($post, '1.1.1.1', ['referrer' => 'https://www.google.com/'], self::PHONE)->assertOk(); // same reader, same day
        $this->recordView($post, '2.2.2.2', ['utm_source' => 'newsletter'])->assertOk();
        $this->recordView($post, '3.3.3.3', [], 'Googlebot/2.1')->assertOk();                                   // bot: ignored

        $rows = PostView::withoutGlobalScope('tenant')->orderBy('id')->get(['source', 'referrer_host', 'device'])->toArray();
        $this->assertSame([
            ['source' => 'search', 'referrer_host' => 'google.com', 'device' => 'mobile'],
            ['source' => 'newsletter', 'referrer_host' => null, 'device' => 'desktop'],
        ], $rows);

        // Unpublished posts aren't counted.
        $draft = $this->makePost($this->ava, 'Draft', 'draft');
        Sanctum::actingAs($this->ava);
        $this->recordView($draft, '4.4.4.4')->assertOk();
        $this->assertSame(0, PostView::withoutGlobalScope('tenant')->where('post_id', $draft->id)->count());
    }

    public function test_brand_report_and_author_scope(): void
    {
        $a1 = $this->makePost($this->ava, 'Ava one');
        $b1 = $this->makePost($this->ben, 'Ben one');
        $this->recordView($a1, '1.1.1.1', ['referrer' => 'https://t.co/x']);
        $this->recordView($a1, '2.2.2.2');
        $this->recordView($b1, '1.1.1.1');
        $this->inAcme(fn () => NewsletterSubscriber::create(['email' => 'n@x.test', 'status' => 'subscribed', 'token' => 't1'])
            ->forceFill(['confirmed_at' => now()])->save());

        Sanctum::actingAs($this->editor);
        $r = $this->getJson('/api/admin/analytics?range=7d', $this->h)->assertOk();

        $this->assertSame(['value' => 3, 'previous' => 0], $r->json('kpis.views'));
        $this->assertSame(2, $r->json('kpis.visitors.value'));
        $this->assertSame(1, $r->json('kpis.new_subscribers.value'));
        $this->assertCount(7, $r->json('daily'));
        $this->assertSame(3, array_sum(array_column($r->json('daily'), 'views')));
        $this->assertSame(['Ava one', 'Ben one'], array_column($r->json('top_posts'), 'title'));
        $this->assertSame(['ava', 'ben'], array_column($r->json('authors'), 'handle'));
        $sources = array_column($r->json('sources'), 'views', 'key');
        $this->assertSame(['direct' => 2, 'social' => 1, 'search' => 0, 'newsletter' => 0, 'referral' => 0], $sources);
        $this->assertSame([['host' => 't.co', 'source' => 'social', 'views' => 1]], $r->json('referrers'));

        // Editors can narrow to one author.
        $this->getJson('/api/admin/analytics?author=ben', $this->h)->assertJsonPath('kpis.views.value', 1);

        // An author sees only their own posts, and no brand-wide newsletter or author ranking.
        Sanctum::actingAs($this->ava);
        $mine = $this->getJson('/api/me/analytics?range=30d', $this->h)->assertOk();
        $this->assertSame(2, $mine->json('kpis.views.value'));
        $this->assertArrayNotHasKey('new_subscribers', $mine->json('kpis'));
        $this->assertSame([], $mine->json('authors'));
        $this->assertSame(['Ava one'], array_column($mine->json('top_posts'), 'title'));
    }

    public function test_previous_period_comparison(): void
    {
        $post = $this->makePost($this->ava, 'Old');
        $this->inAcme(fn () => PostView::create(['post_id' => $post->id, 'ip_address' => '9.9.9.9', 'source' => 'direct', 'device' => 'desktop'])
            ->forceFill(['viewed_at' => now()->subDays(10)])->save());
        $this->recordView($post, '1.1.1.1');

        Sanctum::actingAs($this->editor);
        $this->getJson('/api/admin/analytics?range=7d', $this->h)->assertJsonPath('kpis.views', ['value' => 1, 'previous' => 1]);
    }

    public function test_permissions_and_brand_isolation(): void
    {
        $this->getJson('/api/admin/analytics', $this->h)->assertUnauthorized();

        Sanctum::actingAs($this->ava); // not a member of the brand's staff
        $this->getJson('/api/admin/analytics', $this->h)->assertForbidden();

        Tenant::create(['name' => 'Other', 'slug' => 'other', 'status' => 'active']);
        Sanctum::actingAs($this->editor);
        $this->getJson('/api/admin/analytics', ['X-Tenant' => 'other'])->assertForbidden();
        $this->getJson('/api/admin/analytics?range=1y', $this->h)->assertUnprocessable();
    }
}
