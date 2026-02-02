<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Conversation Export - {{ $child->familyMember->full_name ?? 'Child' }}</title>
    <style>
        body {
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 12px;
            line-height: 1.5;
            color: #333;
            margin: 0;
            padding: 20px;
        }
        .header {
            text-align: center;
            border-bottom: 2px solid #4f46e5;
            padding-bottom: 20px;
            margin-bottom: 30px;
        }
        .header h1 {
            margin: 0 0 10px 0;
            color: #1e293b;
            font-size: 24px;
        }
        .header p {
            margin: 5px 0;
            color: #64748b;
        }
        .meta-info {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 30px;
        }
        .meta-info table {
            width: 100%;
            border-collapse: collapse;
        }
        .meta-info td {
            padding: 5px 10px;
        }
        .meta-info .label {
            font-weight: bold;
            color: #64748b;
            width: 120px;
        }
        .messages {
            margin-bottom: 30px;
        }
        .message {
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 15px;
            page-break-inside: avoid;
        }
        .message-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 1px solid #e2e8f0;
            padding-bottom: 10px;
            margin-bottom: 10px;
        }
        .sender-name {
            font-weight: bold;
            color: #1e293b;
        }
        .message-meta {
            color: #64748b;
            font-size: 10px;
        }
        .category-badge {
            display: inline-block;
            padding: 2px 8px;
            border-radius: 4px;
            font-size: 10px;
            font-weight: bold;
        }
        .category-General { background: #dbeafe; color: #1d4ed8; }
        .category-Schedule { background: #ede9fe; color: #7c3aed; }
        .category-Medical { background: #fee2e2; color: #dc2626; }
        .category-Expense { background: #dcfce7; color: #16a34a; }
        .category-Emergency { background: #fed7aa; color: #ea580c; }
        .message-content {
            color: #334155;
            white-space: pre-wrap;
        }
        .attachments {
            margin-top: 10px;
            padding-top: 10px;
            border-top: 1px solid #e2e8f0;
        }
        .attachment {
            display: inline-block;
            background: #f1f5f9;
            padding: 5px 10px;
            border-radius: 4px;
            margin-right: 5px;
            margin-bottom: 5px;
            font-size: 10px;
        }
        .edit-history {
            margin-top: 10px;
            padding: 10px;
            background: #fffbeb;
            border: 1px solid #fcd34d;
            border-radius: 4px;
            font-size: 10px;
        }
        .edit-history-title {
            font-weight: bold;
            color: #92400e;
            margin-bottom: 5px;
        }
        .read-receipts {
            margin-top: 10px;
            font-size: 10px;
            color: #16a34a;
        }
        .footer {
            text-align: center;
            border-top: 1px solid #e2e8f0;
            padding-top: 20px;
            margin-top: 30px;
            color: #64748b;
            font-size: 10px;
        }
        .page-break {
            page-break-after: always;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Secure Message Export</h1>
        <p>Co-parenting Communication Record</p>
        <p>Generated: {{ now()->format('F j, Y \a\t g:i A T') }}</p>
    </div>

    <div class="meta-info">
        <table>
            <tr>
                <td class="label">Child:</td>
                <td>{{ $child->familyMember->full_name ?? 'N/A' }}</td>
            </tr>
            @if($conversation->subject)
            <tr>
                <td class="label">Subject:</td>
                <td>{{ $conversation->subject }}</td>
            </tr>
            @endif
            <tr>
                <td class="label">Participants:</td>
                <td>
                    @foreach($participants as $participant)
                        {{ $participant->name }}{{ !$loop->last ? ', ' : '' }}
                    @endforeach
                </td>
            </tr>
            <tr>
                <td class="label">Date Range:</td>
                <td>
                    @if(isset($startDate) && isset($endDate))
                        {{ $startDate->format('M j, Y') }} - {{ $endDate->format('M j, Y') }}
                    @else
                        {{ $messages->first()?->created_at->format('M j, Y') ?? 'N/A' }} - {{ $messages->last()?->created_at->format('M j, Y') ?? 'N/A' }}
                    @endif
                </td>
            </tr>
            <tr>
                <td class="label">Total Messages:</td>
                <td>{{ $messages->count() }}</td>
            </tr>
        </table>
    </div>

    <div class="messages">
        <h2 style="color: #1e293b; margin-bottom: 15px;">Messages</h2>

        @foreach($messages as $message)
        <div class="message">
            <div class="message-header">
                <div>
                    <span class="sender-name">{{ $message->sender->name }}</span>
                    <span class="category-badge category-{{ $message->category }}">{{ $message->category }}</span>
                </div>
                @if(!isset($includeTimestamps) || $includeTimestamps)
                <div class="message-meta">
                    {{ $message->created_at->format('M j, Y \a\t g:i A') }}
                    @if($message->ip_address)
                    <br>IP: {{ $message->ip_address }}
                    @endif
                </div>
                @endif
            </div>

            <div class="message-content">{{ $message->content }}</div>

            @if($message->attachments->count() > 0)
            <div class="attachments">
                <strong>Attachments:</strong>
                @foreach($message->attachments as $attachment)
                <span class="attachment">{{ $attachment->original_filename }} ({{ $attachment->formatted_size }})</span>
                @endforeach
            </div>
            @endif

            @if((!isset($includeEditHistory) || $includeEditHistory) && $message->edits->count() > 0)
            <div class="edit-history">
                <div class="edit-history-title">Edit History ({{ $message->edits->count() }} edit{{ $message->edits->count() > 1 ? 's' : '' }})</div>
                @foreach($message->edits as $edit)
                <div style="margin-top: 5px;">
                    <strong>{{ $edit->created_at->format('M j, Y g:i A') }}:</strong><br>
                    Previous: "{{ Str::limit($edit->previous_content, 100) }}"<br>
                    Changed to: "{{ Str::limit($edit->new_content, 100) }}"
                </div>
                @endforeach
            </div>
            @endif

            @if((!isset($includeReadReceipts) || $includeReadReceipts) && $message->reads->count() > 0)
            <div class="read-receipts">
                Read by:
                @foreach($message->reads as $read)
                    {{ $read->user->name }} ({{ $read->read_at->format('M j, g:i A') }}){{ !$loop->last ? ', ' : '' }}
                @endforeach
            </div>
            @endif
        </div>
        @endforeach
    </div>

    <div class="footer">
        <p>This document is an official export from Family Ledger's Secure Messaging system.</p>
        <p>All messages are encrypted at rest and logged with timestamps for record-keeping purposes.</p>
        <p>Document ID: CONV-{{ $conversation->id }}-{{ now()->format('YmdHis') }}</p>
    </div>
</body>
</html>
