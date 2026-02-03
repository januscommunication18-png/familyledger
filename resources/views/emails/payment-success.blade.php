<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment Confirmation</title>
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
        .success-header {
            background: linear-gradient(135deg, #22c55e 0%, #16a34a 100%);
            border-radius: 12px;
            padding: 24px;
            text-align: center;
            margin: 20px 0;
            color: #ffffff;
        }
        .success-header h2 {
            margin: 0 0 8px 0;
            font-size: 22px;
        }
        .success-header p {
            margin: 0;
            opacity: 0.9;
            font-size: 14px;
        }
        .checkmark {
            font-size: 48px;
            margin-bottom: 10px;
        }
        .invoice-box {
            background: #f8fafc;
            border-radius: 8px;
            padding: 24px;
            margin: 20px 0;
        }
        .invoice-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 1px solid #e2e8f0;
        }
        .invoice-number {
            font-size: 14px;
            color: #64748b;
        }
        .invoice-date {
            font-size: 14px;
            color: #64748b;
        }
        .invoice-row {
            display: flex;
            justify-content: space-between;
            padding: 10px 0;
            border-bottom: 1px solid #e2e8f0;
        }
        .invoice-row:last-child {
            border-bottom: none;
        }
        .invoice-row.total {
            font-weight: bold;
            font-size: 18px;
            margin-top: 10px;
            padding-top: 15px;
            border-top: 2px solid #e2e8f0;
            border-bottom: none;
        }
        .invoice-label {
            color: #475569;
        }
        .invoice-value {
            color: #1e293b;
            font-weight: 500;
        }
        .discount {
            color: #22c55e;
        }
        .plan-details {
            background: #eff6ff;
            border-radius: 8px;
            padding: 16px;
            margin: 20px 0;
        }
        .plan-details h4 {
            margin: 0 0 8px 0;
            color: #1e40af;
            font-size: 14px;
        }
        .plan-details p {
            margin: 0;
            color: #3b82f6;
            font-weight: 600;
        }
        .period-info {
            font-size: 13px;
            color: #64748b;
            margin-top: 8px;
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
        @media only screen and (max-width: 480px) {
            .invoice-header {
                flex-direction: column;
                align-items: flex-start;
                gap: 8px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="logo">
            <h1>Family Ledger</h1>
        </div>

        <div class="success-header">
            <div class="checkmark">&#10003;</div>
            <h2>Payment Successful!</h2>
            <p>Thank you for your payment</p>
        </div>

        <p>Hello {{ $user?->name ?? 'Valued Customer' }},</p>

        <p>We've received your payment. Here's your invoice for your records:</p>

        <div class="invoice-box">
            <div class="invoice-header">
                <div class="invoice-number">
                    <strong>Invoice #{{ $invoice->invoice_number }}</strong>
                </div>
                <div class="invoice-date">
                    {{ $invoice->paid_at?->format('F j, Y') ?? $invoice->created_at->format('F j, Y') }}
                </div>
            </div>

            <div class="invoice-row">
                <span class="invoice-label">{{ $plan?->name ?? 'Subscription' }} ({{ ucfirst($invoice->billing_cycle) }})</span>
                <span class="invoice-value">{{ $invoice->formatted_subtotal }}</span>
            </div>

            @if($invoice->discount_amount > 0)
                <div class="invoice-row">
                    <span class="invoice-label">
                        Discount
                        @if($invoice->discount_code)
                            ({{ $invoice->discount_code }} - {{ $invoice->discount_percentage }}% off)
                        @endif
                    </span>
                    <span class="invoice-value discount">{{ $invoice->formatted_discount }}</span>
                </div>
            @endif

            @if($invoice->tax_amount > 0)
                <div class="invoice-row">
                    <span class="invoice-label">Tax</span>
                    <span class="invoice-value">{{ $invoice->formatted_tax }}</span>
                </div>
            @endif

            <div class="invoice-row total">
                <span class="invoice-label">Total Paid</span>
                <span class="invoice-value">{{ $invoice->formatted_total }} {{ $invoice->currency }}</span>
            </div>
        </div>

        <div class="plan-details">
            <h4>Your Plan</h4>
            <p>{{ $plan?->name ?? 'Premium' }} - {{ ucfirst($invoice->billing_cycle) }}</p>
            @if($invoice->period_start && $invoice->period_end)
                <p class="period-info">
                    Subscription period: {{ $invoice->period_start->format('M j, Y') }} - {{ $invoice->period_end->format('M j, Y') }}
                </p>
            @endif
        </div>

        <a href="{{ config('app.url') }}/subscription" class="cta-button">View Your Subscription</a>

        <div class="info-box">
            <strong>Need Help?</strong><br>
            If you have any questions about your subscription or billing, please contact our support team.
        </div>

        <div class="footer">
            <p>This email confirms your payment to Family Ledger.</p>
            <p>&copy; {{ date('Y') }} Family Ledger. All rights reserved.</p>
            <p style="margin-top: 15px;">
                <small>
                    Payment processed by Paddle.com<br>
                    @if($invoice->paddle_transaction_id)
                        Transaction ID: {{ $invoice->paddle_transaction_id }}
                    @endif
                </small>
            </p>
        </div>
    </div>
</body>
</html>
