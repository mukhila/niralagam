<?php

namespace App\Support\Newsletter;

use App\Mail\NewsletterDigestMail;
use App\Models\NewsletterSubscriber;
use App\Models\Post;
use App\Models\Tenant;
use App\Support\Tenancy\CurrentTenant;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Mail;
use Throwable;

/**
 * A brand's digest: posts published since the last one, emailed to confirmed
 * subscribers. Sent weekly by `newsletter:send-digest`, or on demand from the admin.
 * State lives in tenants.settings['newsletter'] = { digest_enabled, last_digest_at }.
 */
class Digest
{
    public const MAX_POSTS = 10;

    public static function settings(Tenant $tenant): array
    {
        return array_merge(['digest_enabled' => true, 'last_digest_at' => null], $tenant->settings['newsletter'] ?? []);
    }

    public static function since(Tenant $tenant): Carbon
    {
        $last = self::settings($tenant)['last_digest_at'];

        return $last ? Carbon::parse($last) : now()->subWeek();
    }

    /** Live posts since the last digest, newest first. */
    public function posts(Tenant $tenant): Collection
    {
        return $this->inTenant($tenant, fn () => Post::live()
            ->with('user:id,name')
            ->where('published_at', '>', self::since($tenant))
            ->latest('published_at')
            ->take(self::MAX_POSTS)
            ->get());
    }

    /**
     * Sends the digest to every confirmed subscriber. Returns how many were sent,
     * or 0 (and sends nothing) when there are no new posts.
     */
    public function send(Tenant $tenant): int
    {
        $posts = $this->posts($tenant);
        if ($posts->isEmpty()) {
            return 0;
        }

        $sent = 0;
        $this->inTenant($tenant, function () use ($tenant, $posts, &$sent) {
            NewsletterSubscriber::subscribed()->chunkById(200, function ($subscribers) use ($tenant, $posts, &$sent) {
                foreach ($subscribers as $subscriber) {
                    try {
                        // queue(): runs in the background once QUEUE_CONNECTION is a real queue.
                        Mail::to($subscriber->email)->queue(new NewsletterDigestMail($subscriber, $tenant, $posts));
                        $subscriber->forceFill(['last_sent_at' => now()])->save();
                        $sent++;
                    } catch (Throwable $e) {
                        report($e);
                    }
                }
            });
        });

        $settings = $tenant->settings ?? [];
        $settings['newsletter'] = array_merge(self::settings($tenant), ['last_digest_at' => now()->toIso8601String()]);
        $tenant->forceFill(['settings' => $settings])->save();

        return $sent;
    }

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
}
