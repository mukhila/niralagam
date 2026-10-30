<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Support\Audit\Audit;
use App\Models\NewsletterSubscriber;
use App\Models\Tenant;
use App\Support\Newsletter\Digest;
use App\Support\Tenancy\CurrentTenant;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

/** Brand admins: the current brand's newsletter list and digest. */
class SubscriberController extends Controller
{
    public function __construct(private Digest $digest) {}

    private function tenant(): Tenant
    {
        return app(CurrentTenant::class)->get();
    }

    public function index(Request $request): JsonResponse
    {
        $list = NewsletterSubscriber::query()
            ->when($request->status && $request->status !== 'all', fn ($q) => $q->where('status', $request->status))
            ->when($request->search, fn ($q, $s) => $q->where('email', 'like', "%{$s}%"))
            ->latest()
            ->paginate(min((int) ($request->per_page ?? 25), 100));

        $counts = NewsletterSubscriber::query()->selectRaw('status, COUNT(*) as n')->groupBy('status')->pluck('n', 'status');

        return response()->json([
            ...$list->toArray(),
            'counts' => [
                'subscribed'   => (int) ($counts['subscribed'] ?? 0),
                'pending'      => (int) ($counts['pending'] ?? 0),
                'unsubscribed' => (int) ($counts['unsubscribed'] ?? 0),
            ],
        ]);
    }

    public function destroy(NewsletterSubscriber $subscriber): JsonResponse
    {
        Audit::record('subscriber.removed', $subscriber, ['status' => [$subscriber->status, null]]);
        $subscriber->delete();

        return response()->json(['message' => 'Subscriber removed.']);
    }

    /** CSV of confirmed subscribers, for moving to another mail tool. */
    public function export(): StreamedResponse
    {
        $name = $this->tenant()->slug.'-subscribers-'.now()->format('Y-m-d').'.csv';

        // Exports hold personal data, so they are always audited.
        Audit::record('subscribers.exported', null, ['count' => [null, NewsletterSubscriber::subscribed()->count()]]);

        return response()->streamDownload(function () {
            $out = fopen('php://output', 'w');
            fputcsv($out, ['email', 'confirmed_at', 'last_sent_at']);
            NewsletterSubscriber::subscribed()->orderBy('id')->chunkById(500, function ($rows) use ($out) {
                foreach ($rows as $s) {
                    fputcsv($out, [$s->email, $s->confirmed_at?->toDateTimeString(), $s->last_sent_at?->toDateTimeString()]);
                }
            });
            fclose($out);
        }, $name, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    /** Digest settings and what the next digest would contain. */
    public function digest(): JsonResponse
    {
        $tenant = $this->tenant();

        return response()->json([
            ...Digest::settings($tenant),
            'since' => Digest::since($tenant)->toIso8601String(),
            'posts' => $this->digest->posts($tenant)->map->only('id', 'title', 'slug', 'published_at')->values(),
            'recipients' => NewsletterSubscriber::subscribed()->count(),
        ]);
    }

    public function updateDigest(Request $request): JsonResponse
    {
        $data   = $request->validate(['digest_enabled' => 'required|boolean']);
        $tenant = $this->tenant();

        $settings = $tenant->settings ?? [];
        $settings['newsletter'] = array_merge(Digest::settings($tenant), ['digest_enabled' => (bool) $data['digest_enabled']]);
        $tenant->forceFill(['settings' => $settings])->save();
        Audit::record('newsletter.digest_toggled', null, ['digest_enabled' => [! $data['digest_enabled'], (bool) $data['digest_enabled']]]);

        return $this->digest();
    }

    public function sendDigest(): JsonResponse
    {
        $tenant = $this->tenant();

        if ($this->digest->posts($tenant)->isEmpty()) {
            return response()->json(['message' => 'There are no new posts since the last digest.'], 422);
        }
        if (NewsletterSubscriber::subscribed()->doesntExist()) {
            return response()->json(['message' => 'There are no confirmed subscribers yet.'], 422);
        }

        $sent = $this->digest->send($tenant);
        Audit::record('newsletter.digest_sent', null, ['recipients' => [null, $sent]]);

        return response()->json(['message' => "Digest sent to {$sent} subscriber".($sent === 1 ? '' : 's').'.', 'sent' => $sent]);
    }
}
