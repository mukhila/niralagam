<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Mail\NewsletterConfirmMail;
use App\Models\NewsletterSubscriber;
use App\Support\Tenancy\CurrentTenant;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Throwable;

/** Reader side of a brand's newsletter: sign up, confirm (double opt-in), unsubscribe. */
class NewsletterController extends Controller
{
    // Same reply whether or not the address is already on the list, so the form
    // can't be used to find out who subscribes.
    private const CHECK_INBOX = 'Almost done! Check your inbox and click the link to confirm your subscription.';

    public function subscribe(Request $request): JsonResponse
    {
        $data  = $request->validate(['email' => 'required|email:rfc|max:255']);
        $email = Str::lower($data['email']);
        $tenant = app(CurrentTenant::class)->get();

        $subscriber = NewsletterSubscriber::firstOrNew(['email' => $email]);

        if ($subscriber->status !== 'subscribed') {
            $subscriber->fill(['tenant_id' => $tenant->id, 'status' => 'pending', 'token' => NewsletterSubscriber::newToken()]);
            $subscriber->save();

            try {
                Mail::to($email)->send(new NewsletterConfirmMail($subscriber, $tenant));
            } catch (Throwable $e) {
                report($e);
            }
        }

        return response()->json(['message' => self::CHECK_INBOX]);
    }

    public function confirm(string $token): JsonResponse
    {
        $subscriber = $this->find($token);

        if ($subscriber->status !== 'subscribed') {
            $subscriber->forceFill(['status' => 'subscribed', 'confirmed_at' => now(), 'unsubscribed_at' => null])->save();
        }

        return response()->json(['message' => "You're subscribed to {$subscriber->tenant->name}.", 'brand' => $subscriber->tenant->name]);
    }

    public function unsubscribe(string $token): JsonResponse
    {
        $subscriber = $this->find($token);

        if ($subscriber->status !== 'unsubscribed') {
            $subscriber->forceFill(['status' => 'unsubscribed', 'unsubscribed_at' => now()])->save();
        }

        return response()->json(['message' => "You've been unsubscribed from {$subscriber->tenant->name}. Sorry to see you go.", 'brand' => $subscriber->tenant->name]);
    }

    private function find(string $token): NewsletterSubscriber
    {
        // Tokens are unique across brands, and links may be opened from any host.
        return NewsletterSubscriber::withoutGlobalScope('tenant')->with('tenant')->where('token', $token)->first()
            ?? abort(404, 'This link is not valid or has been replaced by a newer one.');
    }
}
