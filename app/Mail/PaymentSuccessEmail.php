<?php

namespace App\Mail;

use App\Models\Invoice;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class PaymentSuccessEmail extends Mailable
{
    use Queueable, SerializesModels;

    public Invoice $invoice;
    public ?User $user;
    public ?Tenant $tenant;

    /**
     * Create a new message instance.
     */
    public function __construct(Invoice $invoice, ?User $user = null, ?Tenant $tenant = null)
    {
        $this->invoice = $invoice;
        $this->user = $user ?? $invoice->user;
        $this->tenant = $tenant ?? $invoice->tenant;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Payment Confirmation - Invoice #' . $this->invoice->invoice_number,
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.payment-success',
            with: [
                'invoice' => $this->invoice,
                'user' => $this->user,
                'tenant' => $this->tenant,
                'plan' => $this->invoice->packagePlan,
            ],
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
