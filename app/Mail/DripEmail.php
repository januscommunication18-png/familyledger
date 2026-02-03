<?php

namespace App\Mail;

use App\Models\Backoffice\DripEmailStep;
use App\Models\Backoffice\DripEmailLog;
use App\Models\User;
use App\Models\Tenant;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class DripEmail extends Mailable
{
    use SerializesModels;

    public DripEmailStep $step;
    public ?User $user;
    public ?Tenant $tenant;
    public ?DripEmailLog $log;

    public function __construct(
        DripEmailStep $step,
        ?User $user = null,
        ?Tenant $tenant = null,
        ?DripEmailLog $log = null
    ) {
        $this->step = $step;
        $this->user = $user;
        $this->tenant = $tenant;
        $this->log = $log;
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: $this->parseVariables($this->step->subject),
        );
    }

    public function content(): Content
    {
        $body = $this->parseVariables($this->step->body);

        // Wrap links for click tracking if we have a log entry
        if ($this->log) {
            $body = $this->wrapLinksForTracking($body);
            // Add tracking pixel
            $trackingPixel = '<img src="' . route('email.track.open', $this->log->tracking_token) . '" width="1" height="1" style="display:none;" alt="" />';
            $body .= $trackingPixel;
        }

        return new Content(
            view: 'emails.drip-email',
            with: [
                'body' => $body,
                'user' => $this->user,
                'tenant' => $this->tenant,
                'unsubscribeUrl' => $this->getUnsubscribeUrl(),
            ],
        );
    }

    public function attachments(): array
    {
        return [];
    }

    protected function parseVariables(string $content): string
    {
        // Extract first name from full name
        $firstName = 'there';
        if ($this->user?->name) {
            $nameParts = explode(' ', $this->user->name);
            $firstName = $nameParts[0];
        }

        $appUrl = config('app.url');

        $variables = [
            '{{first_name}}' => $firstName,
            '{{user_name}}' => $this->user?->name ?? 'Valued Customer',
            '{{user_email}}' => $this->user?->email ?? '',
            '{{tenant_name}}' => $this->tenant?->name ?? 'Your Family',
            '{{plan_name}}' => $this->tenant?->getCurrentPlan()?->name ?? 'Free',
            '{{trial_days_left}}' => $this->tenant?->trialDaysRemaining() ?? '0',
            '{{app_url}}' => $appUrl,
            '{{app_name}}' => config('app.name'),
            '{{unsubscribe_url}}' => $this->getUnsubscribeUrl(),
            // Usage stats for upgrade emails
            '{{member_count}}' => $this->tenant ? \App\Models\FamilyMember::where('tenant_id', $this->tenant->id)->count() : 0,
            '{{member_limit}}' => $this->tenant?->getCurrentPlan()?->max_family_members ?? 5,
            '{{document_count}}' => $this->tenant ? \App\Models\FamilyResource::where('tenant_id', $this->tenant->id)->count() : 0,
            '{{document_limit}}' => $this->tenant?->getCurrentPlan()?->max_documents ?? 10,
            '{{storage_used}}' => $this->getStorageUsed(),
            '{{storage_limit}}' => $this->tenant?->getCurrentPlan()?->max_storage_mb ?? 100 . ' MB',
        ];

        $content = str_replace(array_keys($variables), array_values($variables), $content);

        // Convert relative URLs to absolute URLs (handles href="/path" patterns)
        $content = $this->convertRelativeUrls($content, $appUrl);

        return $content;
    }

    /**
     * Convert relative URLs in href attributes to absolute URLs
     */
    protected function convertRelativeUrls(string $content, string $appUrl): string
    {
        // Remove trailing slash from app URL
        $appUrl = rtrim($appUrl, '/');

        // Convert href="/path" to href="https://domain.com/path"
        $content = preg_replace_callback(
            '/href=["\']\/([^"\']*)["\']/',
            function ($matches) use ($appUrl) {
                return 'href="' . $appUrl . '/' . $matches[1] . '"';
            },
            $content
        );

        // Convert src="/path" to src="https://domain.com/path"
        $content = preg_replace_callback(
            '/src=["\']\/([^"\']*)["\']/',
            function ($matches) use ($appUrl) {
                return 'src="' . $appUrl . '/' . $matches[1] . '"';
            },
            $content
        );

        return $content;
    }

    protected function getStorageUsed(): string
    {
        if (!$this->tenant) {
            return '0 MB';
        }

        // Calculate storage used (simplified)
        $bytes = \App\Models\FamilyResource::where('tenant_id', $this->tenant->id)
            ->withSum('files', 'file_size')
            ->get()
            ->sum('files_sum_file_size') ?? 0;

        if ($bytes < 1024 * 1024) {
            return round($bytes / 1024, 1) . ' KB';
        }
        return round($bytes / (1024 * 1024), 1) . ' MB';
    }

    protected function wrapLinksForTracking(string $content): string
    {
        if (!$this->log) {
            return $content;
        }

        // Find all href attributes and wrap them with tracking
        $pattern = '/href=["\']([^"\']+)["\']/i';

        return preg_replace_callback($pattern, function ($matches) {
            $originalUrl = $matches[1];

            // Skip mailto:, tel:, and anchor links
            if (preg_match('/^(mailto:|tel:|#|javascript:)/i', $originalUrl)) {
                return $matches[0];
            }

            // Skip the tracking URLs themselves to avoid infinite loops
            if (str_contains($originalUrl, '/email/track/')) {
                return $matches[0];
            }

            $trackingUrl = route('email.track.click', $this->log->tracking_token) . '?url=' . urlencode($originalUrl);
            return 'href="' . $trackingUrl . '"';
        }, $content);
    }

    protected function getUnsubscribeUrl(): string
    {
        // For now, return the app URL. You can implement a proper unsubscribe mechanism later.
        return config('app.url') . '/unsubscribe';
    }
}
