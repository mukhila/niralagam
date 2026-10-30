<?php

namespace App\Mail;

use App\Models\Post;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Str;

/** "New post from …" for a reader who follows the brand or the author. */
class NewPostAlertMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public Post $post, public User $reader) {}

    public function envelope(): Envelope
    {
        return new Envelope(subject: "New from {$this->post->user?->name} on {$this->post->tenant->name}: {$this->post->title}");
    }

    public function content(): Content
    {
        $base = $this->post->tenant->publicUrl();

        return new Content(view: 'emails.new-post-alert', with: [
            'url'       => $base.'/blog/'.rawurlencode($this->post->slug).'?utm_source=follow-alert&utm_medium=email',
            'authorUrl' => $this->post->user ? $base.'/author/'.rawurlencode($this->post->user->handle()) : null,
            'manageUrl' => $base.'/user/following',
            'summary'   => $this->post->meta_description ?: $this->post->excerpt ?: Str::limit(trim(strip_tags($this->post->content)), 200),
            'image'     => $this->post->og_image ?: $this->post->featured_image,
        ]);
    }
}
