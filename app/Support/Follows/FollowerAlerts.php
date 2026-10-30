<?php

namespace App\Support\Follows;

use App\Mail\NewPostAlertMail;
use App\Models\Follow;
use App\Models\Post;
use App\Models\User;
use Illuminate\Support\Facades\Mail;
use Throwable;

/**
 * Emails a brand's followers, and the author's followers, when a post goes live.
 * Each reader gets one email per post even if they follow both, and a post only
 * ever triggers alerts once (posts.followers_notified_at).
 */
class FollowerAlerts
{
    public function postPublished(Post $post): int
    {
        if ($post->followers_notified_at || ! $post->isLive()) {
            return 0;
        }

        // Claim the post first so a concurrent save can't send twice.
        $claimed = Post::withoutGlobalScope('tenant')->whereKey($post->id)->whereNull('followers_notified_at')
            ->update(['followers_notified_at' => now()]);
        if (! $claimed) {
            return 0;
        }
        $post->followers_notified_at = now();

        $followerIds = Follow::withoutGlobalScope('tenant')
            ->where('tenant_id', $post->tenant_id)
            ->where(fn ($q) => $q->whereNull('author_id')->orWhere('author_id', $post->user_id))
            ->where('user_id', '!=', $post->user_id)
            ->distinct()
            ->pluck('user_id');

        // A separate copy: loading relations onto the caller's $post (e.g. a slimmed-down
        // user) would break whatever it does next, like the editorial email to the author.
        $post = Post::withoutGlobalScope('tenant')->with(['user:id,name,username', 'tenant'])->find($post->id);
        $sent = 0;

        User::whereIn('id', $followerIds)->where('status', 'active')->chunkById(200, function ($users) use ($post, &$sent) {
            foreach ($users as $user) {
                try {
                    Mail::to($user->email)->queue(new NewPostAlertMail($post, $user));
                    $sent++;
                } catch (Throwable $e) {
                    report($e);
                }
            }
        });

        return $sent;
    }
}
