<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Welcome to FamilyLedger</title>
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
            color: #6366f1;
            margin: 0;
            font-size: 24px;
        }
        h2 {
            color: #1f2937;
            margin-top: 0;
            font-size: 20px;
        }
        .welcome-header {
            background: linear-gradient(135deg, #22c55e 0%, #16a34a 100%);
            border-radius: 12px;
            padding: 24px;
            text-align: center;
            margin: 20px 0;
            color: #ffffff;
        }
        .welcome-header h3 {
            margin: 0 0 8px 0;
            font-size: 18px;
        }
        .welcome-header p {
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
            background: #f0fdf4;
            border-left: 4px solid #22c55e;
            padding: 12px 16px;
            margin: 20px 0;
            border-radius: 0 8px 8px 0;
            font-size: 14px;
            color: #166534;
        }
        .footer {
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #e5e7eb;
            font-size: 12px;
            color: #6b7280;
            text-align: center;
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

        <h2>Welcome to Family Ledger!</h2>

        <p>Hello {{ $userName }},</p>

        <p>Welcome aboard! You now have access to shared family information on Family Ledger.</p>

        <div class="welcome-header">
            <h3>You're All Set!</h3>
            <p>Your collaborator account is active and ready to use</p>
            <span class="role-badge">{{ $roleName }} Access</span>
        </div>

        @if($familyMembers->count() > 0)
            <div class="family-members">
                <h4>You have access to:</h4>
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

        <a href="{{ $dashboardUrl }}" class="cta-button">Go to Dashboard</a>

        <div class="info-box">
            <strong>What can you do?</strong><br>
            As a {{ $roleName }}, you can view shared family information, documents, and important details. Log in anytime to access the information you need.
        </div>

        <p>If you have any questions, please contact the family administrator who invited you.</p>

        <div class="footer">
            <p>&copy; {{ date('Y') }} Family Ledger. All rights reserved.</p>
        </div>
    </div>
</body>
</html>
