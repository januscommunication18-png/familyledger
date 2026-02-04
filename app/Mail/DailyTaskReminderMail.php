<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Collection;

class DailyTaskReminderMail extends Mailable
{
    use SerializesModels;

    public User $user;
    public Collection $tasks;
    public Collection $taskOccurrences;
    public Collection $overdueTasks;
    public Collection $overdueOccurrences;
    public string $dateFormatted;

    /**
     * Create a new message instance.
     */
    public function __construct(
        User $user,
        Collection $tasks,
        Collection $taskOccurrences,
        Collection $overdueTasks,
        Collection $overdueOccurrences
    ) {
        $this->user = $user;
        $this->tasks = $tasks;
        $this->taskOccurrences = $taskOccurrences;
        $this->overdueTasks = $overdueTasks;
        $this->overdueOccurrences = $overdueOccurrences;
        $this->dateFormatted = now()->format('l, F j, Y');
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        $taskCount = $this->tasks->count() + $this->taskOccurrences->count();
        $overdueCount = $this->overdueTasks->count() + $this->overdueOccurrences->count();

        $subject = "Your Tasks for Today - {$this->dateFormatted}";

        if ($overdueCount > 0) {
            $subject = "[{$overdueCount} Overdue] " . $subject;
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
            view: 'emails.daily-task-reminder',
        );
    }

    /**
     * Get the attachments for the message.
     */
    public function attachments(): array
    {
        return [];
    }

    /**
     * Get total count of today's tasks.
     */
    public function getTodayTaskCount(): int
    {
        return $this->tasks->count() + $this->taskOccurrences->count();
    }

    /**
     * Get total count of overdue tasks.
     */
    public function getOverdueTaskCount(): int
    {
        return $this->overdueTasks->count() + $this->overdueOccurrences->count();
    }
}
