<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Your Daily Tasks</title>
</head>
<body style="font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; line-height: 1.6; color: #333; max-width: 600px; margin: 0 auto; padding: 20px; background-color: #f5f5f5;">
    <div style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); padding: 30px; border-radius: 10px 10px 0 0; text-align: center;">
        <h1 style="color: white; margin: 0; font-size: 24px;">Your Tasks for Today</h1>
        <p style="color: rgba(255,255,255,0.9); margin: 10px 0 0 0; font-size: 16px;">{{ $dateFormatted }}</p>
    </div>

    <div style="background: #ffffff; padding: 30px; border: 1px solid #e0e0e0; border-top: none; border-radius: 0 0 10px 10px;">
        <p style="font-size: 16px; margin-top: 0;">Hello {{ $user->name ?? 'there' }},</p>

        <p style="color: #666;">Here's your task summary for today:</p>

        <!-- Summary Stats -->
        <div style="display: flex; gap: 15px; margin: 20px 0;">
            <div style="flex: 1; background: #e8f5e9; border-radius: 8px; padding: 15px; text-align: center;">
                <div style="font-size: 28px; font-weight: bold; color: #2e7d32;">{{ $tasks->count() + $taskOccurrences->count() }}</div>
                <div style="font-size: 12px; color: #388e3c; text-transform: uppercase;">Today's Tasks</div>
            </div>
            @if($overdueTasks->count() + $overdueOccurrences->count() > 0)
                <div style="flex: 1; background: #ffebee; border-radius: 8px; padding: 15px; text-align: center;">
                    <div style="font-size: 28px; font-weight: bold; color: #c62828;">{{ $overdueTasks->count() + $overdueOccurrences->count() }}</div>
                    <div style="font-size: 12px; color: #d32f2f; text-transform: uppercase;">Overdue</div>
                </div>
            @endif
        </div>

        <!-- Overdue Tasks Section -->
        @if($overdueTasks->count() > 0 || $overdueOccurrences->count() > 0)
            <div style="margin: 25px 0;">
                <h2 style="color: #c62828; font-size: 18px; margin-bottom: 15px; padding-bottom: 10px; border-bottom: 2px solid #ffcdd2;">
                    Overdue Tasks
                </h2>

                @foreach($overdueTasks as $task)
                    <div style="background: #fff5f5; border-left: 4px solid #c62828; padding: 12px 15px; margin-bottom: 10px; border-radius: 0 6px 6px 0;">
                        <div style="font-weight: 600; color: #333;">{{ $task->title }}</div>
                        <div style="font-size: 13px; color: #666; margin-top: 5px;">
                            @if($task->due_date)
                                <span style="color: #c62828;">Due: {{ $task->due_date->format('M j, Y') }}</span>
                                @if($task->due_time) at {{ $task->due_time }} @endif
                            @endif
                            @if($task->category)
                                <span style="margin-left: 10px; background: #f0f0f0; padding: 2px 8px; border-radius: 4px; font-size: 11px;">{{ $task->category_name }}</span>
                            @endif
                            @if($task->priority && $task->priority !== 'medium')
                                <span style="margin-left: 5px; background: {{ $task->priority === 'urgent' ? '#ffebee' : ($task->priority === 'high' ? '#fff3e0' : '#f5f5f5') }}; color: {{ $task->priority === 'urgent' ? '#c62828' : ($task->priority === 'high' ? '#ef6c00' : '#666') }}; padding: 2px 8px; border-radius: 4px; font-size: 11px;">{{ ucfirst($task->priority) }}</span>
                            @endif
                        </div>
                    </div>
                @endforeach

                @foreach($overdueOccurrences as $occurrence)
                    <div style="background: #fff5f5; border-left: 4px solid #c62828; padding: 12px 15px; margin-bottom: 10px; border-radius: 0 6px 6px 0;">
                        <div style="font-weight: 600; color: #333;">{{ $occurrence->task->title }}</div>
                        <div style="font-size: 13px; color: #666; margin-top: 5px;">
                            <span style="color: #c62828;">Due: {{ $occurrence->scheduled_date->format('M j, Y') }}</span>
                            @if($occurrence->scheduled_time) at {{ $occurrence->scheduled_time }} @endif
                            <span style="margin-left: 10px; background: #e3f2fd; padding: 2px 8px; border-radius: 4px; font-size: 11px; color: #1565c0;">Recurring</span>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif

        <!-- Today's Tasks Section -->
        @if($tasks->count() > 0 || $taskOccurrences->count() > 0)
            <div style="margin: 25px 0;">
                <h2 style="color: #667eea; font-size: 18px; margin-bottom: 15px; padding-bottom: 10px; border-bottom: 2px solid #e8eaf6;">
                    Today's Tasks
                </h2>

                @foreach($tasks as $task)
                    <div style="background: #f8f9ff; border-left: 4px solid #667eea; padding: 12px 15px; margin-bottom: 10px; border-radius: 0 6px 6px 0;">
                        <div style="font-weight: 600; color: #333;">{{ $task->title }}</div>
                        <div style="font-size: 13px; color: #666; margin-top: 5px;">
                            @if($task->due_time)
                                <span>Due at {{ $task->due_time }}</span>
                            @else
                                <span>Due today</span>
                            @endif
                            @if($task->category)
                                <span style="margin-left: 10px; background: #f0f0f0; padding: 2px 8px; border-radius: 4px; font-size: 11px;">{{ $task->category_name }}</span>
                            @endif
                            @if($task->priority && $task->priority !== 'medium')
                                <span style="margin-left: 5px; background: {{ $task->priority === 'urgent' ? '#ffebee' : ($task->priority === 'high' ? '#fff3e0' : '#f5f5f5') }}; color: {{ $task->priority === 'urgent' ? '#c62828' : ($task->priority === 'high' ? '#ef6c00' : '#666') }}; padding: 2px 8px; border-radius: 4px; font-size: 11px;">{{ ucfirst($task->priority) }}</span>
                            @endif
                            @if($task->assignedTo)
                                <span style="margin-left: 5px; color: #888;">Assigned to: {{ $task->assignedTo->first_name }}</span>
                            @endif
                        </div>
                        @if($task->description)
                            <div style="font-size: 13px; color: #888; margin-top: 8px; padding-top: 8px; border-top: 1px dashed #ddd;">
                                {{ Str::limit($task->description, 100) }}
                            </div>
                        @endif
                    </div>
                @endforeach

                @foreach($taskOccurrences as $occurrence)
                    <div style="background: #f8f9ff; border-left: 4px solid #667eea; padding: 12px 15px; margin-bottom: 10px; border-radius: 0 6px 6px 0;">
                        <div style="font-weight: 600; color: #333;">
                            {{ $occurrence->task->title }}
                            <span style="background: #e3f2fd; padding: 2px 8px; border-radius: 4px; font-size: 11px; color: #1565c0; margin-left: 8px;">Recurring</span>
                        </div>
                        <div style="font-size: 13px; color: #666; margin-top: 5px;">
                            @if($occurrence->scheduled_time)
                                <span>Due at {{ $occurrence->scheduled_time }}</span>
                            @else
                                <span>Due today</span>
                            @endif
                            @if($occurrence->task->category)
                                <span style="margin-left: 10px; background: #f0f0f0; padding: 2px 8px; border-radius: 4px; font-size: 11px;">{{ $occurrence->task->category_name }}</span>
                            @endif
                            @if($occurrence->assignee)
                                <span style="margin-left: 5px; color: #888;">Assigned to: {{ $occurrence->assignee->first_name }}</span>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        @endif

        @if($tasks->count() === 0 && $taskOccurrences->count() === 0 && $overdueTasks->count() === 0 && $overdueOccurrences->count() === 0)
            <div style="text-align: center; padding: 30px; background: #f5f5f5; border-radius: 8px; margin: 20px 0;">
                <div style="font-size: 48px; margin-bottom: 10px;">&#127881;</div>
                <p style="color: #666; margin: 0;">No tasks scheduled for today. Enjoy your day!</p>
            </div>
        @endif

        <!-- CTA Button -->
        <div style="text-align: center; margin: 30px 0;">
            <a href="{{ config('app.url') }}/reminders" style="display: inline-block; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 14px 30px; text-decoration: none; border-radius: 6px; font-weight: 600; font-size: 16px;">
                View All Tasks
            </a>
        </div>

        <hr style="border: none; border-top: 1px solid #e0e0e0; margin: 30px 0;">

        <p style="color: #999; font-size: 12px; text-align: center;">
            This is your daily task reminder from Family Ledger.<br>
            You're receiving this because you have tasks scheduled for today.<br>
            <a href="{{ config('app.url') }}/settings/notifications" style="color: #667eea;">Manage notification preferences</a>
        </p>
    </div>
</body>
</html>
