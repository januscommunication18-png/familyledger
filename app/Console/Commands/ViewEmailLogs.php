<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class ViewEmailLogs extends Command
{
    protected $signature = 'email:logs
                            {--lines=100 : Number of emails to show}
                            {--subject= : Filter by subject (partial match)}
                            {--raw : Show raw log content instead of parsed}';

    protected $description = 'View emails from the Laravel log file (when MAIL_MAILER=log)';

    public function handle(): int
    {
        $logFile = storage_path('logs/laravel.log');

        if (!file_exists($logFile)) {
            $this->error('Log file not found: ' . $logFile);
            return 1;
        }

        $this->info('');
        $this->info('==============================================');
        $this->info('  EMAIL LOG VIEWER');
        $this->info('==============================================');
        $this->info('');

        if (config('mail.default') !== 'log') {
            $this->warn('Note: Current mail driver is "' . config('mail.default') . '", not "log"');
            $this->warn('Use --log with test:emails or change MAIL_MAILER=log in .env');
            $this->info('');
        }

        $lines = (int) $this->option('lines');
        $subjectFilter = $this->option('subject');
        $showRaw = $this->option('raw');

        // Read the log file content
        $content = file_get_contents($logFile);

        if ($showRaw) {
            $this->showRawEmails($content, $lines);
            return 0;
        }

        // Parse email entries using regex
        $emails = $this->parseEmails($content);

        if (empty($emails)) {
            $this->warn('No emails found in the log file.');
            $this->info('');
            $this->info('Tips:');
            $this->info('  1. Run: php artisan test:emails --type=all --log');
            $this->info('  2. Or set MAIL_MAILER=log in .env, then test');
            $this->info('  3. Use --raw to see raw log entries');
            return 0;
        }

        // Filter by subject
        if ($subjectFilter) {
            $emails = array_filter($emails, function ($email) use ($subjectFilter) {
                return stripos($email['subject'] ?? '', $subjectFilter) !== false;
            });
        }

        // Limit results
        $emails = array_slice($emails, -$lines);

        if (empty($emails)) {
            $this->warn('No emails found matching your criteria.');
            return 0;
        }

        $this->info('Found ' . count($emails) . ' email(s):');
        $this->info('');

        // Display as table
        $tableData = [];
        foreach ($emails as $email) {
            $tableData[] = [
                $email['date'] ?? 'N/A',
                $email['to'] ?? 'N/A',
                substr($email['subject'] ?? 'N/A', 0, 60) . (strlen($email['subject'] ?? '') > 60 ? '...' : ''),
            ];
        }

        $this->table(['Date', 'To', 'Subject'], $tableData);

        $this->info('');
        $this->info('To see full email content, use: --raw');

        return 0;
    }

    protected function parseEmails(string $content): array
    {
        $emails = [];

        // Find all email blocks by looking for "From:" lines in log entries
        // Pattern: [timestamp] level: From: sender \n To: recipient \n Subject: subject
        $pattern = '/\[(\d{4}-\d{2}-\d{2}\s+\d{2}:\d{2}:\d{2})\][^\n]*From:\s*([^\n]+)\nTo:\s*([^\n]+)\nSubject:\s*([^\n]+)/';

        if (preg_match_all($pattern, $content, $matches, PREG_SET_ORDER)) {
            foreach ($matches as $match) {
                $emails[] = [
                    'date' => $match[1],
                    'from' => trim($match[2]),
                    'to' => trim($match[3]),
                    'subject' => trim($match[4]),
                ];
            }
        }

        return $emails;
    }

    protected function showRawEmails(string $content, int $limit): void
    {
        // Find Subject lines with context
        $lines = explode("\n", $content);
        $emailStarts = [];

        foreach ($lines as $index => $line) {
            if (preg_match('/^\[.*\].*From:/', $line)) {
                $emailStarts[] = $index;
            }
        }

        // Get last N emails
        $emailStarts = array_slice($emailStarts, -$limit);

        if (empty($emailStarts)) {
            $this->warn('No emails found in raw log.');
            return;
        }

        $this->info('Showing last ' . count($emailStarts) . ' email(s):');
        $this->info('');

        foreach ($emailStarts as $i => $startIndex) {
            $this->info('--- Email #' . ($i + 1) . ' ---');

            // Show 10 lines from each email start
            for ($j = 0; $j < 10 && isset($lines[$startIndex + $j]); $j++) {
                $this->line($lines[$startIndex + $j]);
            }
            $this->info('');
        }
    }
}
