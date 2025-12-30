<?php

namespace App\Notifications;

use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * Notification for sending OTP codes via email.
 */
class OtpNotification extends Notification
{

    protected string $code;
    protected string $type;

    /**
     * Create a new notification instance.
     */
    public function __construct(string $code, string $type = 'login')
    {
        $this->code = $code;
        $this->type = $type;
    }

    /**
     * Get the notification's delivery channels.
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $subject = match ($this->type) {
            'login' => 'Your Login Code',
            'verify' => 'Verify Your Email',
            'password_reset' => 'Reset Your Password',
            default => 'Your Verification Code',
        };

        $greeting = match ($this->type) {
            'login' => 'Sign in to your account',
            'verify' => 'Verify your email address',
            'password_reset' => 'Reset your password',
            default => 'Verification required',
        };

        return (new MailMessage)
            ->subject("Family Ledger - {$subject}")
            ->greeting("Hello!")
            ->line($greeting)
            ->line("Your verification code is:")
            ->line("**{$this->code}**")
            ->line("This code will expire in 10 minutes.")
            ->line("If you didn't request this code, please ignore this email.")
            ->salutation("Best regards,\nThe Family Ledger Team");
    }

    /**
     * Get the array representation of the notification.
     */
    public function toArray(object $notifiable): array
    {
        return [
            'code' => $this->code,
            'type' => $this->type,
        ];
    }
}
