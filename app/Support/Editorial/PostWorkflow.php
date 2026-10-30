<?php

namespace App\Support\Editorial;

use App\Mail\PostWorkflowMail;
use App\Models\Post;
use App\Models\User;
use App\Support\Tenancy\CurrentTenant;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Mail;
use Throwable;

/**
 * Editorial rules for a brand's posts:
 *   authors write drafts and submit them for review;
 *   editors/admins publish, schedule or reject (with a note).
 * Authors may edit their own posts only until they are published or scheduled.
 */
class PostWorkflow
{
    /** Brand roles that review and publish (super admins always count). */
    public const MANAGER_ROLES = ['admin', 'editor'];

    private function tenant(): CurrentTenant
    {
        return app(CurrentTenant::class);
    }

    public function isManager(?User $user): bool
    {
        return $this->tenant()->userHasRole($user, self::MANAGER_ROLES);
    }

    /** May see the post even when it isn't live. */
    public function canView(?User $user, Post $post): bool
    {
        return $post->isLive() || ($user && ($user->id === $post->user_id || $this->isManager($user)));
    }

    /** May change the post's content (and delete it). */
    public function canEdit(?User $user, Post $post): bool
    {
        if (! $user) {
            return false;
        }

        return $this->isManager($user)
            || ($user->id === $post->user_id && in_array($post->status, Post::AUTHOR_EDITABLE, true));
    }

    /** Statuses this user may set directly through create/update ('rejected' needs the reject action and a note). */
    public function allowedStatuses(?User $user): array
    {
        return $this->isManager($user)
            ? ['draft', 'pending', 'scheduled', 'published']
            : Post::AUTHOR_STATUSES;
    }

    /**
     * Sets status-dependent fields. $publishAt is required for 'scheduled'
     * (validated as a future time by the caller).
     */
    public function applyStatus(Post $post, string $status, ?Carbon $publishAt = null): void
    {
        $previous     = $post->status;
        $post->status = $status;

        match ($status) {
            'pending'   => $post->submitted_at = now(),
            'scheduled' => $post->published_at = $publishAt,
            // Keep the original date when re-publishing; never a future date for "now".
            'published' => $post->published_at = ($post->published_at && $post->published_at->isPast() && $previous !== 'scheduled')
                ? $post->published_at
                : now(),
            default     => null,
        };

        if ($status === 'pending') {
            $post->review_note = null;
        }
    }

    /** Records the editor's decision on a post. */
    public function review(Post $post, User $reviewer, string $decision, ?string $note = null, ?Carbon $publishAt = null): void
    {
        $this->applyStatus($post, $decision, $publishAt);
        $post->reviewed_by = $reviewer->id;
        $post->reviewed_at = now();
        $post->review_note = $note;
        $post->save();

        if ($post->user_id !== $reviewer->id) {
            $this->send([$post->user], new PostWorkflowMail($post, $decision, $note, $reviewer));
        }
    }

    /** Tells the brand's editors and admins that a post is waiting for review. */
    public function notifySubmitted(Post $post, User $author): void
    {
        $tenant = $this->tenant()->get();
        if (! $tenant) {
            return;
        }

        $reviewers = $tenant->users()
            ->wherePivotIn('role', self::MANAGER_ROLES)
            ->whereKeyNot($author->id)
            ->get();

        $this->send($reviewers, new PostWorkflowMail($post, 'submitted', null, $author));
    }

    /** Email is best-effort: a mail failure must not undo the editorial action. */
    private function send(iterable $users, PostWorkflowMail $mail): void
    {
        foreach ($users as $user) {
            if (! $user?->email) {
                continue;
            }
            try {
                Mail::to($user->email)->send(clone $mail);
            } catch (Throwable $e) {
                report($e);
            }
        }
    }
}
