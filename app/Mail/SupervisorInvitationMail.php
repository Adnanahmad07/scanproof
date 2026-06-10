<?php

namespace App\Mail;

use App\Models\SupervisorInvitation;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class SupervisorInvitationMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public SupervisorInvitation $invitation
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'You\'ve Been Invited to Join ScanProof as Supervisor',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.supervisor-invitation',
            with: [
                'invitation' => $this->invitation,
                'setPasswordUrl' => url('/supervisor/set-password/' . $this->invitation->token),
                'expiresAt' => $this->invitation->expires_at->format('F j, Y \a\t g:i A'),
                'invitedByName' => $this->invitation->invitedBy->name ?? 'Admin',
            ],
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
