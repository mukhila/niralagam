<?php

namespace App\Mail;

use App\Models\NewsletterSubscriber;
use App\Models\Tenant;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

/** Double opt-in: the address joins the list only after this link is clicked. */
class NewsletterConfirmMail extends Mailable
{
    public function __construct(public NewsletterSubscriber $subscriber, public Tenant $tenant) {}

    public function envelope(): Envelope
    {
        return new Envelope(subject: "Confirm your subscription to {$this->tenant->name}");
    }

    public function content(): Content
    {
        return new Content(view: 'emails.newsletter-confirm', with: ['url' => $this->subscriber->confirmUrl()]);
    }
}
