<?php

namespace App\Mail;

use App\Models\Backoffice\Admin;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Collection;

class AdminDailyDigestMail extends Mailable
{
    use SerializesModels;

    public Admin $admin;
    public Collection $newSignups;
    public Collection $todayPayments;
    public Collection $pendingPayments;
    public array $stats;
    public string $dateFormatted;

    /**
     * Create a new message instance.
     */
    public function __construct(
        Admin $admin,
        Collection $newSignups,
        Collection $todayPayments,
        Collection $pendingPayments,
        array $stats
    ) {
        $this->admin = $admin;
        $this->newSignups = $newSignups;
        $this->todayPayments = $todayPayments;
        $this->pendingPayments = $pendingPayments;
        $this->stats = $stats;
        $this->dateFormatted = now()->format('l, F j, Y');
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        $subject = "Admin Daily Digest - {$this->dateFormatted}";

        // Add alert if there are pending payments
        if ($this->pendingPayments->count() > 0) {
            $subject = "[{$this->pendingPayments->count()} Pending] " . $subject;
        }

        return new Envelope(
            subject: $subject,
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.admin-daily-digest',
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
