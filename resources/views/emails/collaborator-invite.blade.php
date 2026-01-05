<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>You're Invited to Join a Family Circle</title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
            background-color: #f5f5f5;
        }
        .container {
            background: #ffffff;
            border-radius: 12px;
            padding: 40px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }
        .logo {
            text-align: center;
            margin-bottom: 30px;
        }
        .logo h1 {
            color: #6366f1;
            margin: 0;
            font-size: 24px;
        }
        h2 {
            color: #1f2937;
            margin-top: 0;
            font-size: 20px;
        }
        .invite-header {
            background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%);
            border-radius: 12px;
            padding: 24px;
            text-align: center;
            margin: 20px 0;
            color: #ffffff;
        }
        .invite-header h3 {
            margin: 0 0 8px 0;
            font-size: 18px;
        }
        .invite-header p {
            margin: 0;
            opacity: 0.9;
            font-size: 14px;
        }
        .role-badge {
            display: inline-block;
            background: rgba(255, 255, 255, 0.2);
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 12px;
            margin-top: 12px;
        }
        .family-members {
            background: #f8fafc;
            border-radius: 8px;
            padding: 16px;
            margin: 20px 0;
        }
        .family-members h4 {
            margin: 0 0 12px 0;
            color: #475569;
            font-size: 14px;
        }
        .member-list {
            list-style: none;
            padding: 0;
            margin: 0;
        }
        .member-list li {
            padding: 8px 0;
            border-bottom: 1px solid #e2e8f0;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .member-list li:last-child {
            border-bottom: none;
        }
        .member-avatar {
            width: 32px;
            height: 32px;
            background: #6366f1;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #fff;
            font-weight: bold;
            font-size: 14px;
        }
        .message-box {
            background: #f0fdf4;
            border-left: 4px solid #22c55e;
            padding: 16px;
            margin: 20px 0;
            border-radius: 0 8px 8px 0;
        }
        .message-box p {
            margin: 0;
            font-style: italic;
            color: #166534;
        }
        .message-box .from {
            font-style: normal;
            font-weight: 600;
            margin-top: 8px;
            font-size: 14px;
        }
        .cta-button {
            display: block;
            background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%);
            color: #ffffff !important;
            text-decoration: none;
            padding: 16px 32px;
            border-radius: 8px;
            text-align: center;
            font-weight: 600;
            font-size: 16px;
            margin: 30px 0;
        }
        .cta-button:hover {
            opacity: 0.9;
        }
        .info-box {
            background: #fef3c7;
            border-left: 4px solid #f59e0b;
            padding: 12px 16px;
            margin: 20px 0;
            border-radius: 0 8px 8px 0;
            font-size: 14px;
            color: #92400e;
        }
        .footer {
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #e5e7eb;
            font-size: 12px;
            color: #6b7280;
            text-align: center;
        }
        .link-text {
            word-break: break-all;
            font-size: 12px;
            color: #6b7280;
            margin-top: 10px;
        }
        p {
            margin: 10px 0;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="logo">
            <h1>Family Ledger</h1>
        </div>

        <h2>You're Invited!</h2>

        <p>Hello{{ $invite->first_name ? ' ' . $invite->first_name : '' }},</p>

        <p><strong>{{ $inviterName }}</strong> has invited you to join their Family Circle on Family Ledger.</p>

        <div class="invite-header">
            <h3>Family Circle Invitation</h3>
            <p>You've been invited as {{ $invite->relationship_info['label'] ?? 'a collaborator' }}</p>
            <span class="role-badge">{{ $roleName }} Access</span>
        </div>

        @if($invite->message)
            <div class="message-box">
                <p>"{{ $invite->message }}"</p>
                <p class="from">— {{ $inviterName }}</p>
            </div>
        @endif

        @if($familyMembers->count() > 0)
            <div class="family-members">
                <h4>You'll have access to:</h4>
                <ul class="member-list">
                    @foreach($familyMembers as $member)
                        <li>
                            <span class="member-avatar">{{ strtoupper(substr($member->first_name, 0, 1)) }}</span>
                            <span>{{ $member->first_name }} {{ $member->last_name }}</span>
                        </li>
                    @endforeach
                </ul>
            </div>
        @endif

        <a href="{{ $acceptUrl }}" class="cta-button">Accept Invitation</a>

        <p class="link-text">Or copy and paste this link in your browser:<br>{{ $acceptUrl }}</p>

        <div class="info-box">
            <strong>What happens next?</strong><br>
            Click the button above to accept the invitation. If you don't have a Family Ledger account yet, you'll be able to create one. Once set up, you'll be able to view the shared family information.
        </div>

        <p>This invitation will expire in 7 days. If you don't recognize {{ $inviterName }} or didn't expect this invitation, you can safely ignore this email.</p>

        <div class="footer">
            <p>This invitation was sent from Family Ledger on behalf of {{ $inviterName }}.</p>
            <p>&copy; {{ date('Y') }} Family Ledger. All rights reserved.</p>
        </div>
    </div>
</body>
</html>
