<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Co-parenting Invitation</title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 1200px;
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
            color: #ec4899;
            margin: 0;
            font-size: 24px;
        }
        h2 {
            color: #1f2937;
            margin-top: 0;
            font-size: 20px;
        }
        .invite-header {
            background: linear-gradient(135deg, #ec4899 0%, #f43f5e 100%);
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
        .children-section {
            background: #fdf2f8;
            border-radius: 8px;
            padding: 16px;
            margin: 20px 0;
            border: 1px solid #fbcfe8;
        }
        .children-section h4 {
            margin: 0 0 12px 0;
            color: #be185d;
            font-size: 14px;
        }
        .child-list {
            list-style: none;
            padding: 0;
            margin: 0;
        }
        .child-list li {
            padding: 10px 0;
            border-bottom: 1px solid #fbcfe8;
            display: flex;
            align-items: center;
            gap: 12px;
        }
        .child-list li:last-child {
            border-bottom: none;
        }
        .child-avatar {
            width: 40px;
            height: 40px;
            background: linear-gradient(135deg, #ec4899 0%, #f43f5e 100%);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #fff;
            font-weight: bold;
            font-size: 16px;
        }
        .child-info {
            flex: 1;
        }
        .child-name {
            font-weight: 600;
            color: #1f2937;
        }
        .child-age {
            font-size: 13px;
            color: #6b7280;
        }
        .message-box {
            background: #f8fafc;
            border-radius: 8px;
            padding: 16px;
            margin: 20px 0;
            border-left: 4px solid #ec4899;
        }
        .message-box p {
            margin: 0;
            font-style: italic;
            color: #475569;
        }
        .features-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 16px;
            margin: 20px 0;
        }
        .feature-item {
            text-align: center;
            padding: 16px;
            background: #f8fafc;
            border-radius: 8px;
        }
        .feature-icon {
            font-size: 24px;
            margin-bottom: 8px;
        }
        .feature-title {
            font-size: 13px;
            font-weight: 600;
            color: #1f2937;
        }
        .cta-button {
            display: block;
            width: 100%;
            max-width: 300px;
            margin: 30px auto;
            padding: 14px 28px;
            background: linear-gradient(135deg, #ec4899 0%, #f43f5e 100%);
            color: #ffffff !important;
            text-decoration: none;
            text-align: center;
            border-radius: 8px;
            font-weight: 600;
            font-size: 16px;
        }
        .cta-button:hover {
            opacity: 0.9;
        }
        .expiry-notice {
            text-align: center;
            color: #f59e0b;
            font-size: 13px;
            margin: 20px 0;
        }
        .fallback-link {
            background: #f8fafc;
            border-radius: 8px;
            padding: 16px;
            margin: 20px 0;
            word-break: break-all;
            font-size: 12px;
            color: #64748b;
        }
        .footer {
            text-align: center;
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #e2e8f0;
            color: #94a3b8;
            font-size: 13px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="logo">
            <h1>Family Ledger</h1>
            <p style="color: #64748b; font-size: 14px; margin-top: 4px;">Co-parenting Made Simple</p>
        </div>

        <div class="invite-header">
            <h3>{{ $inviterName }} invited you to co-parent</h3>
            <p>You've been invited to share parenting responsibilities on Family Ledger</p>
            <span class="role-badge">Co-parent Access</span>
        </div>

        @if($invite->message)
        <div class="message-box">
            <p>"{{ $invite->message }}"</p>
        </div>
        @endif

        <div class="children-section">
            <h4>Children You'll Have Access To</h4>
            <ul class="child-list">
                @foreach($children as $child)
                <li>
                    <div class="child-avatar">{{ strtoupper(substr($child->first_name ?? 'C', 0, 1)) }}</div>
                    <div class="child-info">
                        <div class="child-name">{{ $child->full_name }}</div>
                        <div class="child-age">{{ $child->age }} years old</div>
                    </div>
                </li>
                @endforeach
            </ul>
        </div>

        <div class="features-grid">
            <div class="feature-item">
                <div class="feature-icon">📅</div>
                <div class="feature-title">Shared Calendar</div>
            </div>
            <div class="feature-item">
                <div class="feature-icon">💰</div>
                <div class="feature-title">Expense Tracking</div>
            </div>
            <div class="feature-item">
                <div class="feature-icon">💬</div>
                <div class="feature-title">Secure Messages</div>
            </div>
        </div>

        <a href="{{ $acceptUrl }}" class="cta-button">Accept Invitation</a>

        <div class="expiry-notice">
            This invitation expires in 7 days
        </div>

        <div class="fallback-link">
            <p>If the button doesn't work, copy and paste this link:</p>
            <p>{{ $acceptUrl }}</p>
        </div>

        <div class="footer">
            <p>This email was sent by Family Ledger because {{ $inviterName }} invited you to co-parent.</p>
            <p>If you didn't expect this email, you can safely ignore it.</p>
        </div>
    </div>
</body>
</html>
