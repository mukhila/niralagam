<?php

namespace App\Mail;

use App\Models\Post;
use App\Models\User;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

/**
 * Editorial notifications.
 *   submitted → to the brand's editors/admins ($actor = author)
 *   published | scheduled | rejected → to the author ($actor = reviewer)
 */
class PostWorkflowMail extends Mailable
{
    public function __construct(
        public Post $post,
        public string $event,
        public ?string $note,
        public User $actor,
    ) {}

    public function envelope(): Envelope
    {
        $title = $this->post->title;

        return new Envelope(subject: match ($this->event) {
            'submitted' => "Review needed: {$title}",
            'published' => "Published: {$title}",
            'scheduled' => "Scheduled: {$title}",
            'rejected'  => "Changes requested: {$title}",
        });
    }

    public function content(): Content
    {
        $base = rtrim(config('tenancy.frontend_url'), '/');

        return new Content(view: 'emails.post-workflow', with: [
            'brand' => $this->post->tenant?->name ?? 'Niralagam',
            'url'   => match ($this->event) {
                'submitted' => "{$base}/admin/posts/{$this->post->id}",
                'published' => "{$base}/blog/{$this->post->slug}",
                default     => "{$base}/user",
            },
        ]);
    }
}
