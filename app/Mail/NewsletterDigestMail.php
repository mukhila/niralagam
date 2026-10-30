<?php

namespace App\Mail;

use App\Models\NewsletterSubscriber;
use App\Models\Tenant;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Mail\Mailables\Headers;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Collection;

/** The periodic digest of a brand's new posts. */
class NewsletterDigestMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public NewsletterSubscriber $subscriber,
        public Tenant $tenant,
        public Collection $posts,
    ) {}

    public function envelope(): Envelope
    {
        $count = $this->posts->count();

        return new Envelope(subject: $count === 1
            ? "{$this->tenant->name}: {$this->posts->first()->title}"
            : "{$this->tenant->name}: {$count} new posts this week");
    }

    /** Lets mail apps show their own "Unsubscribe" button. */
    public function headers(): Headers
    {
        return new Headers(text: ['List-Unsubscribe' => '<'.$this->subscriber->unsubscribeUrl().'>']);
    }

    public function content(): Content
    {
        return new Content(view: 'emails.newsletter-digest', with: [
            'siteUrl'        => $this->tenant->publicUrl(),
            'unsubscribeUrl' => $this->subscriber->unsubscribeUrl(),
        ]);
    }
}
