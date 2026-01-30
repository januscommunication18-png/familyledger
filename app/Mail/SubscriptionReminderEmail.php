<?php

namespace App\Mail;

use App\Models\Tenant;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class SubscriptionReminderEmail extends Mailable
{
    use Queueable, SerializesModels;

    public Tenant $tenant;
    public ?User $user;
    public int $daysRemaining;
    public string $reminderType; // '7_days', '3_days', '0_days'

    /**
     * Create a new message instance.
     */
    public function __construct(Tenant $tenant, ?User $user, int $daysRemaining)
    {
        $this->tenant = $tenant;
        $this->user = $user;
        $this->daysRemaining = $daysRemaining;
        $this->reminderType = $this->getReminderType($daysRemaining);
    }

    /**
     * Get reminder type based on days remaining.
     */
    private function getReminderType(int $days): string
    {
        if ($days < 0) {
            return 'expired';
        } elseif ($days === 0) {
            return '0_days';
        } elseif ($days <= 3) {
            return '3_days';
        } else {
            return '7_days';
        }
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        $subject = match ($this->reminderType) {
            'expired' => 'Your Family Ledger subscription has expired',
            '0_days' => 'Your subscription renewal is due today',
            '3_days' => 'Your subscription renews in 3 days',
            '7_days' => 'Your subscription renews in 7 days',
            default => 'Subscription Reminder',
        };

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
            view: 'emails.subscription-reminder',
            with: [
                'tenant' => $this->tenant,
                'user' => $this->user,
                'daysRemaining' => $this->daysRemaining,
                'reminderType' => $this->reminderType,
                'plan' => $this->tenant->getCurrentPlan(),
                'renewalDate' => $this->tenant->subscription_expires_at,
                'amount' => $this->getNextBillingAmount(),
            ],
        );
    }

    /**
     * Get the next billing amount.
     */
    private function getNextBillingAmount(): string
    {
        $plan = $this->tenant->getCurrentPlan();
        if (!$plan) {
            return '$0.00';
        }

        $amount = $this->tenant->billing_cycle === 'yearly'
            ? $plan->cost_per_year
            : $plan->cost_per_month;

        return '$' . number_format($amount, 2);
    }

    /**
     * Get the attachments for the message.
     */
    public function attachments(): array
    {
        return [];
    }
}
