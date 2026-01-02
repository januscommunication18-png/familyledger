<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Your Login Code</title>
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
        .code-box {
            background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%);
            border-radius: 8px;
            padding: 20px;
            text-align: center;
            margin: 30px 0;
        }
        .code {
            font-size: 36px;
            font-weight: bold;
            letter-spacing: 8px;
            color: #ffffff;
            font-family: 'Courier New', monospace;
        }
        .warning {
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

        <h2>Your Login Verification Code</h2>

        <p>Hello{{ isset($user) && $user->name ? ' ' . $user->name : '' }},</p>

        <p>You requested a verification code to log in to your Family Ledger account. Use the code below to complete your sign-in:</p>

        <div class="code-box">
            <div class="code">{{ $code }}</div>
        </div>

        <p>This code will expire in <strong>10 minutes</strong>.</p>

        <div class="warning">
            <strong>Security Notice:</strong> If you didn't request this code, please ignore this email. Someone may have entered your email address by mistake.
        </div>

        <p>For your security, never share this code with anyone. Family Ledger staff will never ask you for this code.</p>

        <div class="footer">
            <p>This is an automated message from Family Ledger.</p>
            <p>Please do not reply to this email.</p>
        </div>
    </div>
</body>
</html>
