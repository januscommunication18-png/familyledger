<?php

namespace App\Mail;

use App\Models\CollaboratorInvite;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class CollaboratorInviteMail extends Mailable
{
    use Queueable, SerializesModels;

    public CollaboratorInvite $invite;

    /**
     * Create a new message instance.
     */
    public function __construct(CollaboratorInvite $invite)
    {
        $this->invite = $invite;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        $inviterName = $this->invite->inviter->name ?? 'Someone';

        return new Envelope(
            subject: "{$inviterName} invited you to join their Family Circle on FamilyLedger",
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.collaborator-invite',
            with: [
                'invite' => $this->invite,
                'acceptUrl' => route('collaborator.accept', $this->invite->token),
                'inviterName' => $this->invite->inviter->name ?? 'Someone',
                'familyMembers' => $this->invite->familyMembers,
                'roleName' => $this->invite->role_info['label'] ?? 'Viewer',
                'roleDescription' => $this->invite->role_info['description'] ?? '',
            ],
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}
