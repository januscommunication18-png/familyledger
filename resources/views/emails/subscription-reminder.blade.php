<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Subscription Reminder</title>
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
        .reminder-header {
            border-radius: 12px;
            padding: 24px;
            text-align: center;
            margin: 20px 0;
            color: #ffffff;
        }
        .reminder-header.urgent {
            background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
        }
        .reminder-header.warning {
            background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
        }
        .reminder-header.info {
            background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
        }
        .reminder-header h2 {
            margin: 0 0 8px 0;
            font-size: 22px;
        }
        .reminder-header .days {
            font-size: 48px;
            font-weight: bold;
            margin: 10px 0;
        }
        .reminder-header p {
            margin: 0;
            opacity: 0.9;
            font-size: 14px;
        }
        .subscription-box {
            background: #f8fafc;
            border-radius: 8px;
            padding: 24px;
            margin: 20px 0;
        }
        .subscription-row {
            display: flex;
            justify-content: space-between;
            padding: 12px 0;
            border-bottom: 1px solid #e2e8f0;
        }
        .subscription-row:last-child {
            border-bottom: none;
        }
        .subscription-label {
            color: #64748b;
            font-size: 14px;
        }
        .subscription-value {
            color: #1e293b;
            font-weight: 600;
        }
        .amount-highlight {
            background: #eff6ff;
            border-radius: 8px;
            padding: 16px;
            text-align: center;
            margin: 20px 0;
        }
        .amount-highlight .label {
            font-size: 14px;
            color: #64748b;
            margin-bottom: 8px;
        }
        .amount-highlight .amount {
            font-size: 32px;
            font-weight: bold;
            color: #1e40af;
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
        .info-box {
            background: #fef3c7;
            border-left: 4px solid #f59e0b;
            padding: 12px 16px;
            margin: 20px 0;
            border-radius: 0 8px 8px 0;
            font-size: 14px;
            color: #92400e;
        }
        .info-box.danger {
            background: #fee2e2;
            border-left-color: #ef4444;
            color: #991b1b;
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

        @if($reminderType === 'expired')
            <div class="reminder-header urgent">
                <h2>Subscription Expired</h2>
                <div class="days">!</div>
                <p>Your subscription has ended</p>
            </div>
        @elseif($reminderType === '0_days')
            <div class="reminder-header urgent">
                <h2>Payment Due Today</h2>
                <div class="days">!</div>
                <p>Your subscription renewal is due</p>
            </div>
        @elseif($reminderType === '3_days')
            <div class="reminder-header warning">
                <h2>Renewal Coming Soon</h2>
                <div class="days">{{ $daysRemaining }}</div>
                <p>days until your subscription renews</p>
            </div>
        @else
            <div class="reminder-header info">
                <h2>Renewal Reminder</h2>
                <div class="days">{{ $daysRemaining }}</div>
                <p>days until your subscription renews</p>
            </div>
        @endif

        <p>Hello {{ $user?->name ?? 'Valued Customer' }},</p>

        @if($reminderType === 'expired')
            <p>Your Family Ledger subscription has <strong>expired</strong>. To continue enjoying premium features and keep your family organized, please renew your subscription.</p>
        @elseif($reminderType === '0_days')
            <p>This is a reminder that your Family Ledger subscription renewal is due <strong>today</strong>. Please ensure your payment method is up to date to avoid any interruption in service.</p>
        @elseif($reminderType === '3_days')
            <p>Your Family Ledger subscription will automatically renew in <strong>{{ $daysRemaining }} days</strong>. We wanted to give you a heads up so there are no surprises.</p>
        @else
            <p>Your Family Ledger subscription will automatically renew in <strong>{{ $daysRemaining }} days</strong>. Here's a summary of your upcoming renewal:</p>
        @endif

        <div class="subscription-box">
            <div class="subscription-row">
                <span class="subscription-label">Plan</span>
                <span class="subscription-value">{{ $plan?->name ?? 'Premium' }}</span>
            </div>
            <div class="subscription-row">
                <span class="subscription-label">Billing Cycle</span>
                <span class="subscription-value">{{ ucfirst($tenant->billing_cycle ?? 'Monthly') }}</span>
            </div>
            <div class="subscription-row">
                <span class="subscription-label">{{ $reminderType === 'expired' ? 'Expired On' : 'Renewal Date' }}</span>
                <span class="subscription-value">{{ $renewalDate?->format('F j, Y') ?? 'N/A' }}</span>
            </div>
        </div>

        @if($reminderType !== 'expired')
        <div class="amount-highlight">
            <div class="label">Amount to be charged</div>
            <div class="amount">{{ $amount }}</div>
        </div>
        @endif

        <a href="{{ config('app.url') }}/subscription" class="cta-button">{{ $reminderType === 'expired' ? 'Renew Subscription' : 'Manage Subscription' }}</a>

        @if($reminderType === 'expired')
            <div class="info-box danger">
                <strong>Limited Access</strong><br>
                Your account may be limited to free plan features. Renew your subscription now to regain full access to all premium features.
            </div>
        @elseif($reminderType === '0_days')
            <div class="info-box danger">
                <strong>Action Required</strong><br>
                If your payment fails, your account may be downgraded to the free plan. Update your payment method now to ensure uninterrupted access.
            </div>
        @else
            <div class="info-box">
                <strong>Need to make changes?</strong><br>
                You can update your payment method, change your plan, or cancel your subscription at any time from your account settings.
            </div>
        @endif

        <p>If you have any questions or need assistance, please don't hesitate to contact our support team.</p>

        <div class="footer">
            <p>You're receiving this email because you have an active subscription with Family Ledger.</p>
            <p>&copy; {{ date('Y') }} Family Ledger. All rights reserved.</p>
        </div>
    </div>
</body>
</html>
