<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class RecoveryCodeSetMail extends Mailable
{
    use Queueable, SerializesModels;

    public string $userName;
    public string $actionType;
    public string $recoveryCode;

    /**
     * Create a new message instance.
     */
    public function __construct(string $userName, string $recoveryCode, string $actionType = 'set')
    {
        $this->userName = $userName;
        $this->recoveryCode = $recoveryCode;
        $this->actionType = $actionType;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        $action = $this->actionType === 'set' ? 'Set' : 'Updated';
        return new Envelope(
            subject: "Family Ledger - Account Recovery Code {$action}",
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.recovery-code-set',
        );
    }

    /**
     * Get the attachments for the message.
     */
    public function attachments(): array
    {
        return [];
    }
}
