<?php

namespace App\Mail;

use App\Models\CollaboratorInvite;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Collection;

class CoparentInviteMail extends Mailable
{
    use Queueable, SerializesModels;

    public CollaboratorInvite $invite;
    public Collection $children;
    public string $acceptUrl;
    public string $inviterName;

    /**
     * Create a new message instance.
     */
    public function __construct(CollaboratorInvite $invite, Collection $children)
    {
        $this->invite = $invite;
        $this->children = $children;
        $this->acceptUrl = route('collaborator.accept', ['token' => $invite->token]);
        // Load inviter name now to avoid lazy loading issues
        $this->inviterName = $invite->inviter->name ?? 'Someone';
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        $childNames = $this->children->pluck('first_name')->take(2)->join(', ');

        if ($this->children->count() > 2) {
            $childNames .= ' and others';
        }

        return new Envelope(
            subject: "{$this->inviterName} invited you to co-parent {$childNames} on Family Ledger",
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.coparent-invite',
            with: [
                'invite' => $this->invite,
                'children' => $this->children,
                'acceptUrl' => $this->acceptUrl,
                'inviterName' => $this->inviterName,
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
