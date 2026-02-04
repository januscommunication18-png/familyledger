<?php

namespace App\Mail;

use App\Models\Backoffice\DataAccessRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class DataAccessRequestMail extends Mailable
{
    use Queueable, SerializesModels;

    public DataAccessRequest $accessRequest;
    public string $ownerName;

    /**
     * Create a new message instance.
     */
    public function __construct(DataAccessRequest $accessRequest, string $ownerName)
    {
        $this->accessRequest = $accessRequest;
        $this->ownerName = $ownerName;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Data Access Request - Family Ledger Support',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.data-access-request',
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
