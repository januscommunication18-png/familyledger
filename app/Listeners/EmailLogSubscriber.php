<?php

namespace App\Listeners;

use App\Models\Backoffice\EmailLog;
use Illuminate\Mail\Events\MessageSending;
use Illuminate\Mail\Events\MessageSent;
use Illuminate\Support\Facades\Log;

class EmailLogSubscriber
{
    /**
     * Handle the MessageSending event.
     * We mark as sent here because if sending fails, an exception is thrown.
     */
    public function handleMessageSending(MessageSending $event): void
    {
        try {
            $message = $event->message;
            $mailable = $event->data['__mailable'] ?? null;

            // Get recipients
            $to = $message->getTo();
            $toEmail = '';
            $toName = null;

            if (!empty($to)) {
                $address = reset($to);
                if ($address) {
                    $toEmail = $address->getAddress();
                    $toName = $address->getName();
                }
            }

            // Get sender
            $from = $message->getFrom();
            $fromEmail = null;
            $fromName = null;

            if (!empty($from)) {
                $firstFrom = reset($from);
                if ($firstFrom) {
                    $fromEmail = $firstFrom->getAddress();
                    $fromName = $firstFrom->getName();
                }
            }

            // Get mailable class name
            $mailableClass = null;
            if ($mailable) {
                $mailableClass = get_class($mailable);
            }

            // Try to get user_id and tenant_id from mailable or context
            $userId = null;
            $tenantId = null;
            $metadata = [];

            if ($mailable) {
                // Check for common properties
                if (property_exists($mailable, 'user') && $mailable->user) {
                    $userId = $mailable->user->id ?? null;
                    $tenantId = $mailable->user->tenant_id ?? null;
                }
                if (property_exists($mailable, 'tenant') && $mailable->tenant) {
                    $tenantId = $mailable->tenant->id ?? $tenantId;
                }
                if (property_exists($mailable, 'userId')) {
                    $userId = $mailable->userId;
                }
                if (property_exists($mailable, 'tenantId')) {
                    $tenantId = $mailable->tenantId;
                }

                // For drip emails, capture campaign info
                if ($mailableClass === 'App\Mail\DripEmail') {
                    if (property_exists($mailable, 'step') && $mailable->step) {
                        $metadata['drip_campaign_id'] = $mailable->step->drip_campaign_id ?? null;
                        $metadata['drip_step_id'] = $mailable->step->id ?? null;
                    }
                }
            }

            // Get body content
            $bodyHtml = $message->getHtmlBody();
            $bodyText = $message->getTextBody();

            // Detect mailable type from class or subject
            $mailableType = null;

            // Check if it's a drip campaign email first
            if (!empty($metadata) && $this->isDripEmail($metadata)) {
                $mailableType = 'Drip Campaign';
            } elseif ($mailableClass) {
                $mailableType = EmailLog::$mailableTypes[$mailableClass] ?? class_basename($mailableClass);
            } else {
                // Fallback: detect from subject
                $subject = $message->getSubject() ?? '';
                $mailableType = $this->detectTypeFromSubject($subject);
            }

            // Create the log entry and mark as sent immediately
            // If the email fails to send, Laravel throws an exception and this won't complete
            EmailLog::create([
                'mailable_class' => $mailableClass,
                'mailable_type' => $mailableType,
                'to_email' => $toEmail,
                'to_name' => $toName,
                'from_email' => $fromEmail,
                'from_name' => $fromName,
                'subject' => $message->getSubject() ?? '',
                'body_html' => $bodyHtml,
                'body_text' => $bodyText,
                'tenant_id' => $tenantId,
                'user_id' => $userId,
                'status' => EmailLog::STATUS_SENT,
                'sent_at' => now(),
                'metadata' => !empty($metadata) ? $metadata : null,
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to log email: ' . $e->getMessage(), [
                'exception' => $e,
            ]);
        }
    }

    /**
     * Handle the MessageSent event.
     * Used to update message ID after successful send.
     */
    public function handleMessageSent(MessageSent $event): void
    {
        try {
            // Get the message ID if available
            $messageId = null;
            try {
                $messageId = $event->sent->getMessageId();
            } catch (\Exception $e) {
                // Some transports don't support message ID
                return;
            }

            if (!$messageId) {
                return;
            }

            // Get recipient email to find the log
            $to = $event->message->getTo();
            if (empty($to)) {
                return;
            }

            $address = reset($to);
            $toEmail = $address ? $address->getAddress() : null;

            if (!$toEmail) {
                return;
            }

            // Update the most recent log for this email with the message ID
            $log = EmailLog::where('to_email', $toEmail)
                ->where('status', EmailLog::STATUS_SENT)
                ->whereNull('message_id')
                ->latest()
                ->first();

            if ($log) {
                $log->update(['message_id' => $messageId]);
            }

        } catch (\Exception $e) {
            Log::error('Failed to update email message ID: ' . $e->getMessage(), [
                'exception' => $e,
            ]);
        }
    }

    /**
     * Detect email type from subject line.
     */
    protected function detectTypeFromSubject(string $subject): string
    {
        $typeMap = [
            'Reset Your Backoffice Password' => 'Password Reset',
            'Reset Your Password' => 'Password Reset',
            'Access Verification' => 'Access Code',
            'Security Code' => 'Security Code',
            'Collaborator Invite' => 'Collaborator Invite',
            'Collaborator Reminder' => 'Collaborator Reminder',
            'Welcome to' => 'Welcome',
            'Co-Parent Invite' => 'Co-Parent Invite',
            'Payment Success' => 'Payment Success',
            'Subscription Reminder' => 'Subscription Reminder',
            'Recovery Code' => 'Recovery Code',
            'Verify Your Email' => 'Email Verification',
            'Data Access Request' => 'Data Access Request',
            'Your Tasks for Today' => 'Daily Task Reminder',
            'Admin Daily Digest' => 'Admin Daily Digest',
        ];

        foreach ($typeMap as $keyword => $type) {
            if (str_contains($subject, $keyword)) {
                return $type;
            }
        }

        return 'System Email';
    }

    /**
     * Detect if this is a drip campaign email from metadata.
     */
    protected function isDripEmail(array $metadata): bool
    {
        return !empty($metadata['drip_campaign_id']) || !empty($metadata['drip_step_id']);
    }

    /**
     * Register the listeners for the subscriber.
     */
    public function subscribe($events): array
    {
        return [
            MessageSending::class => 'handleMessageSending',
            MessageSent::class => 'handleMessageSent',
        ];
    }
}
