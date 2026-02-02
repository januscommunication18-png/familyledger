<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use PhpOffice\PhpWord\PhpWord;
use PhpOffice\PhpWord\IOFactory;
use PhpOffice\PhpWord\Style\Font;

class GenerateEmailDocument extends Command
{
    protected $signature = 'emails:generate-doc {--output=Family_Ledger_Email_Templates.docx : Output file name}';
    protected $description = 'Generate a Word document with all email templates';

    public function handle()
    {
        $phpWord = new PhpWord();

        // Define styles
        $phpWord->addTitleStyle(1, ['bold' => true, 'size' => 24, 'color' => '4F46E5'], ['spaceBefore' => 0, 'spaceAfter' => 240]);
        $phpWord->addTitleStyle(2, ['bold' => true, 'size' => 18, 'color' => '1F2937'], ['spaceBefore' => 480, 'spaceAfter' => 120]);
        $phpWord->addTitleStyle(3, ['bold' => true, 'size' => 14, 'color' => '374151'], ['spaceBefore' => 240, 'spaceAfter' => 120]);

        // Title Page
        $section = $phpWord->addSection();
        $section->addTextBreak(5);
        $section->addText('Family Ledger', ['bold' => true, 'size' => 36, 'color' => '6366F1'], ['alignment' => 'center']);
        $section->addText('Email Templates Documentation', ['size' => 20, 'color' => '6B7280'], ['alignment' => 'center']);
        $section->addTextBreak(2);
        $section->addText('Generated: ' . now()->format('F j, Y'), ['size' => 12, 'color' => '9CA3AF'], ['alignment' => 'center']);

        // Table of Contents
        $section->addPageBreak();
        $section->addTitle('Table of Contents', 1);
        $section->addTextBreak(1);

        $toc = [
            '1. Authentication & Security Emails',
            '   1.1 MFA/Login Verification Code',
            '   1.2 Recovery Code Set/Updated',
            '   1.3 Backoffice Access Code',
            '   1.4 Backoffice Security Code',
            '2. Invitation Emails',
            '   2.1 Collaborator Invitation',
            '   2.2 Co-parent Invitation',
            '3. Collaborator Emails',
            '   3.1 Collaborator Welcome',
            '   3.2 Collaborator Reminder',
            '4. Billing & Subscription Emails',
            '   4.1 Payment Success / Invoice',
            '   4.2 Subscription Reminder (7 Days)',
            '   4.3 Subscription Reminder (3 Days)',
            '   4.4 Subscription Reminder (Expiry Day)',
            '5. Drip Campaign Emails',
            '6. Other Emails',
            '   6.1 Shopping List',
        ];

        foreach ($toc as $item) {
            $section->addText($item, ['size' => 12], ['spaceBefore' => 60]);
        }

        // 1. Authentication & Security Emails
        $section->addPageBreak();
        $section->addTitle('1. Authentication & Security Emails', 1);

        // 1.1 MFA Code
        $section->addTitle('1.1 MFA/Login Verification Code', 2);
        $this->addEmailDetails($section, [
            'Template File' => 'resources/views/emails/mfa-code.blade.php',
            'Subject' => 'Your Login Code',
            'Trigger' => 'When user requests MFA code for login',
            'Variables' => '$user (optional), $code',
        ]);
        $section->addTitle('Email Content:', 3);
        $section->addText('Header: "Your Login Verification Code"', ['size' => 11]);
        $section->addText('Body: Greeting with user name (if available), explains verification code for login, displays 6-digit code in styled box, notes 10-minute expiration.', ['size' => 11]);
        $section->addText('Security Notice: Warning about not sharing code, notice if user didn\'t request it.', ['size' => 11]);

        // 1.2 Recovery Code
        $section->addTitle('1.2 Recovery Code Set/Updated', 2);
        $this->addEmailDetails($section, [
            'Template File' => 'resources/views/emails/recovery-code-set.blade.php',
            'Subject' => 'Recovery Code [Set/Updated]',
            'Trigger' => 'When user sets or updates their recovery code',
            'Variables' => '$userName, $recoveryCode, $actionType (set/updated)',
        ]);
        $section->addTitle('Email Content:', 3);
        $section->addText('Confirms recovery code has been set/updated, displays 16-character recovery code in formatted groups, security information about storing code safely, warning if user didn\'t make this change.', ['size' => 11]);

        // 1.3 Backoffice Access Code
        $section->addTitle('1.3 Backoffice Access Code', 2);
        $this->addEmailDetails($section, [
            'Template File' => 'resources/views/emails/backoffice-access-code.blade.php',
            'Subject' => 'Access Verification',
            'Trigger' => 'When admin requests backoffice access',
            'Variables' => '$adminName, $code',
        ]);
        $section->addTitle('Email Content:', 3);
        $section->addText('Purple-themed header for backoffice branding, displays access verification code, 5-minute expiration warning, security notice.', ['size' => 11]);

        // 1.4 Backoffice Security Code
        $section->addTitle('1.4 Backoffice Security Code', 2);
        $this->addEmailDetails($section, [
            'Template File' => 'resources/views/emails/backoffice-security-code.blade.php',
            'Subject' => 'Security Code',
            'Trigger' => 'When admin logs into backoffice',
            'Variables' => '$adminName, $code',
        ]);
        $section->addTitle('Email Content:', 3);
        $section->addText('Similar to access code but for login verification, 10-minute expiration.', ['size' => 11]);

        // 2. Invitation Emails
        $section->addPageBreak();
        $section->addTitle('2. Invitation Emails', 1);

        // 2.1 Collaborator Invitation
        $section->addTitle('2.1 Collaborator Invitation', 2);
        $this->addEmailDetails($section, [
            'Template File' => 'resources/views/emails/collaborator-invite.blade.php',
            'Subject' => "You're Invited to Join a Family Circle",
            'Trigger' => 'When family admin invites a collaborator',
            'Variables' => '$invite, $inviterName, $roleName, $familyMembers, $acceptUrl',
        ]);
        $section->addTitle('Email Content:', 3);
        $section->addText('Purple gradient invitation header showing inviter name and role, optional personal message from inviter, list of family members the collaborator will have access to, CTA button to accept invitation, fallback URL for manual entry, 7-day expiration notice.', ['size' => 11]);

        // 2.2 Co-parent Invitation
        $section->addTitle('2.2 Co-parent Invitation', 2);
        $this->addEmailDetails($section, [
            'Template File' => 'resources/views/emails/coparent-invite.blade.php',
            'Subject' => 'Co-parenting Invitation',
            'Trigger' => 'When parent invites co-parent',
            'Variables' => '$invite, $inviterName, $children, $acceptUrl',
        ]);
        $section->addTitle('Email Content:', 3);
        $section->addText('Pink/rose themed header for co-parenting branding, shows children the co-parent will have access to (name, age), optional personal message, features grid showing: Shared Calendar, Expense Tracking, Secure Messages, CTA button to accept, 7-day expiration.', ['size' => 11]);

        // 3. Collaborator Emails
        $section->addPageBreak();
        $section->addTitle('3. Collaborator Emails', 1);

        // 3.1 Collaborator Welcome
        $section->addTitle('3.1 Collaborator Welcome', 2);
        $this->addEmailDetails($section, [
            'Template File' => 'resources/views/emails/collaborator-welcome.blade.php',
            'Subject' => 'Welcome to FamilyLedger',
            'Trigger' => 'When collaborator accepts invitation and account is created',
            'Variables' => '$userName, $roleName, $familyMembers, $dashboardUrl',
        ]);
        $section->addTitle('Email Content:', 3);
        $section->addText('Green "success" themed header showing account is active, lists family members the collaborator can access, CTA to go to dashboard, info box explaining what they can do based on their role.', ['size' => 11]);

        // 3.2 Collaborator Reminder
        $section->addTitle('3.2 Collaborator Reminder', 2);
        $this->addEmailDetails($section, [
            'Template File' => 'resources/views/emails/collaborator-reminder.blade.php',
            'Subject' => 'Reminder: Family Information Available',
            'Trigger' => 'Periodic reminder to check family information',
            'Variables' => '$userName, $roleName, $familyMembers, $dashboardUrl',
        ]);
        $section->addTitle('Email Content:', 3);
        $section->addText('Orange/amber themed reminder header, friendly reminder to check in on family info, lists accessible family members, CTA to view family information, encourages staying connected.', ['size' => 11]);

        // 4. Billing & Subscription Emails
        $section->addPageBreak();
        $section->addTitle('4. Billing & Subscription Emails', 1);

        // 4.1 Payment Success
        $section->addTitle('4.1 Payment Success / Invoice', 2);
        $this->addEmailDetails($section, [
            'Template File' => 'resources/views/emails/payment-success.blade.php',
            'Mail Class' => 'App\\Mail\\PaymentSuccessEmail',
            'Subject' => 'Payment Confirmation - Invoice #[number]',
            'Trigger' => 'After successful payment via Paddle',
            'Variables' => '$invoice, $user, $tenant, $plan',
        ]);
        $section->addTitle('Email Content:', 3);
        $section->addText('Green checkmark success header, invoice details box showing: Invoice number and date, Plan name and billing cycle, Subtotal, Discount (if applicable with code and percentage), Tax (if applicable), Total paid. Plan details section with subscription period, CTA to view subscription, Paddle transaction ID in footer.', ['size' => 11]);

        // 4.2 Subscription Reminder 7 Days
        $section->addTitle('4.2 Subscription Reminder (7 Days)', 2);
        $this->addEmailDetails($section, [
            'Template File' => 'resources/views/emails/subscription-reminder.blade.php',
            'Mail Class' => 'App\\Mail\\SubscriptionReminderEmail',
            'Subject' => 'Your subscription renews in 7 days',
            'Trigger' => '7 days before subscription renewal',
            'Variables' => '$tenant, $user, $daysRemaining, $reminderType, $plan, $renewalDate, $amount',
        ]);
        $section->addTitle('Email Content:', 3);
        $section->addText('Blue "info" themed header with "7" days countdown, subscription details (plan, billing cycle, renewal date), amount to be charged highlighted, CTA to manage subscription, info box about making changes.', ['size' => 11]);

        // 4.3 Subscription Reminder 3 Days
        $section->addTitle('4.3 Subscription Reminder (3 Days)', 2);
        $this->addEmailDetails($section, [
            'Subject' => 'Your subscription renews in 3 days',
            'Trigger' => '3 days before subscription renewal',
        ]);
        $section->addTitle('Email Content:', 3);
        $section->addText('Orange/amber "warning" themed header with countdown, same structure as 7-day reminder but with more urgency.', ['size' => 11]);

        // 4.4 Subscription Reminder Expiry
        $section->addTitle('4.4 Subscription Reminder (Expiry Day / Expired)', 2);
        $this->addEmailDetails($section, [
            'Subject' => 'Your subscription renewal is due today / has expired',
            'Trigger' => 'Day of renewal or after expiration',
        ]);
        $section->addTitle('Email Content:', 3);
        $section->addText('Red "urgent" themed header, warning about payment due or expired status, action required notice about potential downgrade to free plan, CTA to renew subscription.', ['size' => 11]);

        // 5. Drip Campaign Emails
        $section->addPageBreak();
        $section->addTitle('5. Drip Campaign Emails', 1);
        $this->addEmailDetails($section, [
            'Template File' => 'resources/views/emails/drip-email.blade.php',
            'Mail Class' => 'App\\Mail\\DripEmail',
            'Subject' => 'Dynamic - set per campaign step',
            'Trigger' => 'Based on campaign triggers (signup, trial_start, subscription_active, etc.)',
            'Variables' => '$step, $user, $tenant, $log (for tracking)',
        ]);
        $section->addTitle('Template Structure:', 3);
        $section->addText('Purple gradient header with app name, dynamic body content from campaign step, supports full HTML/rich text, built-in email tracking (open pixel, click tracking), unsubscribe link in footer.', ['size' => 11]);

        $section->addTitle('Available Template Variables:', 3);
        $table = $section->addTable(['borderSize' => 1, 'borderColor' => 'E5E7EB']);
        $table->addRow();
        $table->addCell(3000)->addText('Variable', ['bold' => true]);
        $table->addCell(6000)->addText('Description', ['bold' => true]);

        $variables = [
            '{{first_name}}' => 'User\'s first name (extracted from full name)',
            '{{user_name}}' => 'User\'s full name',
            '{{user_email}}' => 'User\'s email address',
            '{{tenant_name}}' => 'Family/tenant name',
            '{{plan_name}}' => 'Current subscription plan name',
            '{{trial_days_left}}' => 'Days remaining in trial',
            '{{app_url}}' => 'Application URL',
            '{{app_name}}' => 'Application name',
            '{{unsubscribe_url}}' => 'Unsubscribe link',
            '{{member_count}}' => 'Number of family members',
            '{{member_limit}}' => 'Family member limit for plan',
            '{{document_count}}' => 'Number of documents stored',
            '{{document_limit}}' => 'Document limit for plan',
            '{{storage_used}}' => 'Storage space used',
            '{{storage_limit}}' => 'Storage limit for plan',
        ];

        foreach ($variables as $var => $desc) {
            $table->addRow();
            $table->addCell(3000)->addText($var, ['size' => 10, 'name' => 'Courier New']);
            $table->addCell(6000)->addText($desc, ['size' => 10]);
        }

        // 6. Other Emails
        $section->addPageBreak();
        $section->addTitle('6. Other Emails', 1);

        // 6.1 Shopping List
        $section->addTitle('6.1 Shopping List', 2);
        $this->addEmailDetails($section, [
            'Template File' => 'resources/views/emails/shopping-list.blade.php',
            'Subject' => 'Shopping List: [List Name]',
            'Trigger' => 'When user shares shopping list via email',
            'Variables' => '$list, $member, $senderName, $personalMessage, $items, $categories',
        ]);
        $section->addTitle('Email Content:', 3);
        $section->addText('Green/teal themed header with list name and optional store name, optional personal message from sender, items organized by category with checkboxes for printing, quantity shown if more than 1.', ['size' => 11]);

        // Email Design Guidelines
        $section->addPageBreak();
        $section->addTitle('Email Design Guidelines', 1);

        $section->addTitle('Color Palette:', 2);
        $colors = [
            'Primary (Indigo)' => '#6366F1 - Used for main branding, CTAs',
            'Success (Green)' => '#22C55E - Payment confirmations, welcome messages',
            'Warning (Amber)' => '#F59E0B - Reminders, attention needed',
            'Danger (Red)' => '#EF4444 - Urgent notices, expirations',
            'Info (Blue)' => '#3B82F6 - General information',
            'Co-parent (Pink)' => '#EC4899 - Co-parenting features',
            'Backoffice (Purple)' => '#9333EA - Admin/backoffice emails',
            'Shopping (Teal)' => '#10B981 - Shopping list features',
        ];
        foreach ($colors as $name => $value) {
            $section->addText($name . ': ' . $value, ['size' => 11]);
        }

        $section->addTitle('Common Elements:', 2);
        $section->addText('- Max width: 600px for main emails, 1200px for data-heavy emails', ['size' => 11]);
        $section->addText('- Border radius: 8-12px for containers', ['size' => 11]);
        $section->addText('- Gradient headers with white text', ['size' => 11]);
        $section->addText('- CTA buttons: Full width or centered, 16px padding, gradient background', ['size' => 11]);
        $section->addText('- Info/warning boxes: Left border accent (4px), subtle background', ['size' => 11]);
        $section->addText('- Footer: Light gray background, copyright, unsubscribe link', ['size' => 11]);

        $section->addTitle('Font Stack:', 2);
        $section->addText("-apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif", ['size' => 10, 'name' => 'Courier New']);

        // Save document
        $outputFile = $this->option('output');
        $outputPath = storage_path('app/' . $outputFile);

        $objWriter = IOFactory::createWriter($phpWord, 'Word2007');
        $objWriter->save($outputPath);

        $this->info("Word document generated successfully!");
        $this->info("File saved to: {$outputPath}");

        return 0;
    }

    private function addEmailDetails($section, array $details): void
    {
        $table = $section->addTable(['borderSize' => 1, 'borderColor' => 'E5E7EB', 'cellMargin' => 80]);

        foreach ($details as $label => $value) {
            $table->addRow();
            $table->addCell(2500)->addText($label . ':', ['bold' => true, 'size' => 10]);
            $table->addCell(6500)->addText($value, ['size' => 10]);
        }

        $section->addTextBreak(1);
    }
}
