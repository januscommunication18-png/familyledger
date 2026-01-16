<?php

namespace App\Mail;

use App\Models\Collaborator;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class CollaboratorReminderMail extends Mailable
{
    use Queueable, SerializesModels;

    public Collaborator $collaborator;

    /**
     * Create a new message instance.
     */
    public function __construct(Collaborator $collaborator)
    {
        $this->collaborator = $collaborator;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Reminder: You have access to family information on FamilyLedger',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.collaborator-reminder',
            with: [
                'collaborator' => $this->collaborator,
                'userName' => $this->collaborator->user->name ?? $this->collaborator->display_name,
                'familyMembers' => $this->collaborator->familyMembers,
                'roleName' => $this->collaborator->role_info['label'] ?? 'Viewer',
                'dashboardUrl' => route('dashboard'),
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
