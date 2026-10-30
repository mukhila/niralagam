<?php

namespace App\Mail;

use App\Models\TenantInvitation;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

class TenantInvitationMail extends Mailable
{
    public function __construct(public TenantInvitation $invitation, public string $url) {}

    public function envelope(): Envelope
    {
        return new Envelope(subject: "You're invited to join {$this->invitation->tenant->name} on Niralagam");
    }

    public function content(): Content
    {
        return new Content(view: 'emails.tenant-invitation');
    }
}
