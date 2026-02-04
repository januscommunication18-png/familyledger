<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Daily Digest</title>
</head>
<body style="font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; line-height: 1.6; color: #333; max-width: 650px; margin: 0 auto; padding: 20px; background-color: #f5f5f5;">
    <div style="background: linear-gradient(135deg, #1e3a5f 0%, #2d5a87 100%); padding: 30px; border-radius: 10px 10px 0 0; text-align: center;">
        <h1 style="color: white; margin: 0; font-size: 24px;">Admin Daily Digest</h1>
        <p style="color: rgba(255,255,255,0.9); margin: 10px 0 0 0; font-size: 16px;">{{ $dateFormatted }}</p>
    </div>

    <div style="background: #ffffff; padding: 30px; border: 1px solid #e0e0e0; border-top: none; border-radius: 0 0 10px 10px;">
        <p style="font-size: 16px; margin-top: 0;">Hello {{ $admin->name }},</p>

        <p style="color: #666;">Here's your daily business summary:</p>

        <!-- Summary Stats -->
        <table style="width: 100%; border-collapse: collapse; margin: 25px 0;">
            <tr>
                <td style="width: 25%; padding: 12px 8px; text-align: center; background: #e3f2fd; border-radius: 8px 0 0 8px;">
                    <div style="font-size: 28px; font-weight: bold; color: #1565c0;">{{ $stats['total_tenants'] }}</div>
                    <div style="font-size: 11px; color: #1976d2; text-transform: uppercase; margin-top: 5px;">Total Families</div>
                </td>
                <td style="width: 25%; padding: 12px 8px; text-align: center; background: #e8f5e9;">
                    <div style="font-size: 28px; font-weight: bold; color: #2e7d32;">{{ $newSignups->count() }}</div>
                    <div style="font-size: 11px; color: #388e3c; text-transform: uppercase; margin-top: 5px;">New Today</div>
                </td>
                <td style="width: 25%; padding: 12px 8px; text-align: center; background: #f3e5f5;">
                    <div style="font-size: 28px; font-weight: bold; color: #7b1fa2;">{{ $stats['today_logins'] }}</div>
                    <div style="font-size: 11px; color: #8e24aa; text-transform: uppercase; margin-top: 5px;">Logins Today</div>
                </td>
                <td style="width: 25%; padding: 12px 8px; text-align: center; background: #fff3e0; border-radius: 0 8px 8px 0;">
                    <div style="font-size: 28px; font-weight: bold; color: #ef6c00;">${{ number_format($stats['today_revenue'], 2) }}</div>
                    <div style="font-size: 11px; color: #f57c00; text-transform: uppercase; margin-top: 5px;">Today's Revenue</div>
                </td>
            </tr>
        </table>

        <!-- New Sign-ups Section -->
        <div style="margin: 30px 0;">
            <h2 style="color: #2e7d32; font-size: 18px; margin-bottom: 15px; padding-bottom: 10px; border-bottom: 2px solid #c8e6c9;">
                New Sign-ups Today ({{ $newSignups->count() }})
            </h2>

            @if($newSignups->count() > 0)
                <table style="width: 100%; border-collapse: collapse;">
                    <thead>
                        <tr style="background: #f5f5f5;">
                            <th style="padding: 10px; text-align: left; font-size: 12px; color: #666; text-transform: uppercase;">Family</th>
                            <th style="padding: 10px; text-align: left; font-size: 12px; color: #666; text-transform: uppercase;">Owner</th>
                            <th style="padding: 10px; text-align: left; font-size: 12px; color: #666; text-transform: uppercase;">Email</th>
                            <th style="padding: 10px; text-align: left; font-size: 12px; color: #666; text-transform: uppercase;">Time</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($newSignups as $tenant)
                            <tr style="border-bottom: 1px solid #eee;">
                                <td style="padding: 12px 10px; font-weight: 600;">{{ $tenant->name }}</td>
                                <td style="padding: 12px 10px;">{{ $tenant->owner->name ?? 'N/A' }}</td>
                                <td style="padding: 12px 10px; color: #666;">{{ $tenant->owner->email ?? 'N/A' }}</td>
                                <td style="padding: 12px 10px; color: #888; font-size: 13px;">{{ $tenant->created_at->format('g:i A') }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @else
                <div style="text-align: center; padding: 20px; background: #f9f9f9; border-radius: 8px; color: #888;">
                    No new sign-ups today
                </div>
            @endif
        </div>

        <!-- Today's Payments Section -->
        <div style="margin: 30px 0;">
            <h2 style="color: #1565c0; font-size: 18px; margin-bottom: 15px; padding-bottom: 10px; border-bottom: 2px solid #bbdefb;">
                Payments Received Today ({{ $todayPayments->count() }}) - ${{ number_format($stats['today_revenue'], 2) }}
            </h2>

            @if($todayPayments->count() > 0)
                <table style="width: 100%; border-collapse: collapse;">
                    <thead>
                        <tr style="background: #f5f5f5;">
                            <th style="padding: 10px; text-align: left; font-size: 12px; color: #666; text-transform: uppercase;">Invoice</th>
                            <th style="padding: 10px; text-align: left; font-size: 12px; color: #666; text-transform: uppercase;">Customer</th>
                            <th style="padding: 10px; text-align: left; font-size: 12px; color: #666; text-transform: uppercase;">Plan</th>
                            <th style="padding: 10px; text-align: right; font-size: 12px; color: #666; text-transform: uppercase;">Amount</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($todayPayments as $invoice)
                            <tr style="border-bottom: 1px solid #eee;">
                                <td style="padding: 12px 10px; font-family: monospace; color: #1565c0;">{{ $invoice->invoice_number }}</td>
                                <td style="padding: 12px 10px;">{{ $invoice->customer_name ?? $invoice->user->name ?? 'N/A' }}</td>
                                <td style="padding: 12px 10px; color: #666;">{{ $invoice->packagePlan->name ?? $invoice->billing_cycle ?? 'N/A' }}</td>
                                <td style="padding: 12px 10px; text-align: right; font-weight: 600; color: #2e7d32;">${{ number_format($invoice->total_amount, 2) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr style="background: #e8f5e9;">
                            <td colspan="3" style="padding: 12px 10px; font-weight: 600;">Total</td>
                            <td style="padding: 12px 10px; text-align: right; font-weight: 700; color: #2e7d32; font-size: 16px;">${{ number_format($stats['today_revenue'], 2) }}</td>
                        </tr>
                    </tfoot>
                </table>
            @else
                <div style="text-align: center; padding: 20px; background: #f9f9f9; border-radius: 8px; color: #888;">
                    No payments received today
                </div>
            @endif
        </div>

        <!-- Pending Payments Section -->
        @if($pendingPayments->count() > 0)
            <div style="margin: 30px 0;">
                <h2 style="color: #c62828; font-size: 18px; margin-bottom: 15px; padding-bottom: 10px; border-bottom: 2px solid #ffcdd2;">
                    Pending Payments ({{ $pendingPayments->count() }}) - ${{ number_format($stats['pending_amount'], 2) }}
                </h2>

                <table style="width: 100%; border-collapse: collapse;">
                    <thead>
                        <tr style="background: #ffebee;">
                            <th style="padding: 10px; text-align: left; font-size: 12px; color: #666; text-transform: uppercase;">Invoice</th>
                            <th style="padding: 10px; text-align: left; font-size: 12px; color: #666; text-transform: uppercase;">Customer</th>
                            <th style="padding: 10px; text-align: left; font-size: 12px; color: #666; text-transform: uppercase;">Due Date</th>
                            <th style="padding: 10px; text-align: right; font-size: 12px; color: #666; text-transform: uppercase;">Amount</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($pendingPayments as $invoice)
                            <tr style="border-bottom: 1px solid #eee;">
                                <td style="padding: 12px 10px; font-family: monospace; color: #c62828;">{{ $invoice->invoice_number }}</td>
                                <td style="padding: 12px 10px;">{{ $invoice->customer_name ?? $invoice->user->name ?? 'N/A' }}</td>
                                <td style="padding: 12px 10px; color: {{ $invoice->due_date && $invoice->due_date->isPast() ? '#c62828' : '#666' }};">
                                    {{ $invoice->due_date ? $invoice->due_date->format('M j, Y') : 'N/A' }}
                                    @if($invoice->due_date && $invoice->due_date->isPast())
                                        <span style="background: #ffebee; color: #c62828; padding: 2px 6px; border-radius: 4px; font-size: 11px; margin-left: 5px;">OVERDUE</span>
                                    @endif
                                </td>
                                <td style="padding: 12px 10px; text-align: right; font-weight: 600; color: #c62828;">${{ number_format($invoice->total_amount, 2) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr style="background: #ffebee;">
                            <td colspan="3" style="padding: 12px 10px; font-weight: 600;">Total Pending</td>
                            <td style="padding: 12px 10px; text-align: right; font-weight: 700; color: #c62828; font-size: 16px;">${{ number_format($stats['pending_amount'], 2) }}</td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        @endif

        <!-- Monthly Stats -->
        <div style="margin: 30px 0; background: #f8f9fa; border-radius: 8px; padding: 20px;">
            <h3 style="margin: 0 0 15px 0; color: #333; font-size: 16px;">Monthly Overview ({{ now()->format('F Y') }})</h3>
            <table style="width: 100%;">
                <tr>
                    <td style="padding: 8px 0; color: #666;">New Sign-ups this month:</td>
                    <td style="padding: 8px 0; text-align: right; font-weight: 600;">{{ $stats['month_signups'] }}</td>
                </tr>
                <tr>
                    <td style="padding: 8px 0; color: #666;">Revenue this month:</td>
                    <td style="padding: 8px 0; text-align: right; font-weight: 600; color: #2e7d32;">${{ number_format($stats['month_revenue'], 2) }}</td>
                </tr>
                <tr>
                    <td style="padding: 8px 0; color: #666;">Active subscriptions:</td>
                    <td style="padding: 8px 0; text-align: right; font-weight: 600;">{{ $stats['active_subscriptions'] }}</td>
                </tr>
            </table>
        </div>

        <!-- CTA Button -->
        <div style="text-align: center; margin: 30px 0;">
            <a href="{{ config('app.url') }}/backoffice/dashboard" style="display: inline-block; background: linear-gradient(135deg, #1e3a5f 0%, #2d5a87 100%); color: white; padding: 14px 30px; text-decoration: none; border-radius: 6px; font-weight: 600; font-size: 16px;">
                View Dashboard
            </a>
        </div>

        <hr style="border: none; border-top: 1px solid #e0e0e0; margin: 30px 0;">

        <p style="color: #999; font-size: 12px; text-align: center;">
            This is your daily admin digest from Family Ledger Backoffice.<br>
            Sent to {{ $admin->email }}
        </p>
    </div>
</body>
</html>
