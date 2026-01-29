<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ config('app.name') }}</title>
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
            overflow: hidden;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }
        .header {
            background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%);
            padding: 30px 40px;
            text-align: center;
        }
        .header h1 {
            color: #ffffff;
            margin: 0;
            font-size: 24px;
            font-weight: 600;
        }
        .header p {
            color: rgba(255, 255, 255, 0.9);
            margin: 8px 0 0 0;
            font-size: 14px;
        }
        .content {
            padding: 40px;
        }
        .content h2 {
            color: #1f2937;
            margin-top: 0;
            font-size: 20px;
        }
        .content p {
            margin: 16px 0;
            color: #4b5563;
        }
        .content a {
            color: #6366f1;
        }
        .content img {
            max-width: 100%;
            height: auto;
        }
        .cta-button {
            display: inline-block;
            background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%);
            color: #ffffff !important;
            text-decoration: none;
            padding: 14px 28px;
            border-radius: 8px;
            text-align: center;
            font-weight: 600;
            font-size: 16px;
            margin: 20px 0;
        }
        .cta-button:hover {
            opacity: 0.9;
        }
        .info-box {
            background: #f0f9ff;
            border-left: 4px solid #6366f1;
            padding: 16px 20px;
            margin: 24px 0;
            border-radius: 0 8px 8px 0;
        }
        .info-box p {
            margin: 0;
            color: #1e40af;
        }
        .divider {
            height: 1px;
            background: #e5e7eb;
            margin: 30px 0;
        }
        .footer {
            background: #f9fafb;
            padding: 24px 40px;
            text-align: center;
            border-top: 1px solid #e5e7eb;
        }
        .footer p {
            margin: 8px 0;
            font-size: 12px;
            color: #6b7280;
        }
        .footer a {
            color: #6b7280;
            text-decoration: underline;
        }
        .social-links {
            margin: 16px 0;
        }
        .social-links a {
            display: inline-block;
            margin: 0 8px;
            color: #6b7280;
        }
        /* Email body content styles */
        .email-body h1, .email-body h2, .email-body h3 {
            color: #1f2937;
        }
        .email-body ul, .email-body ol {
            padding-left: 24px;
            color: #4b5563;
        }
        .email-body li {
            margin: 8px 0;
        }
        .email-body blockquote {
            border-left: 4px solid #e5e7eb;
            padding-left: 16px;
            margin: 16px 0;
            color: #6b7280;
            font-style: italic;
        }
        .email-body table {
            width: 100%;
            border-collapse: collapse;
            margin: 16px 0;
        }
        .email-body th, .email-body td {
            padding: 12px;
            border: 1px solid #e5e7eb;
            text-align: left;
        }
        .email-body th {
            background: #f9fafb;
            font-weight: 600;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>{{ config('app.name') }}</h1>
            <p>Your Family's Digital Hub</p>
        </div>

        <div class="content">
            <div class="email-body">
                {!! $body !!}
            </div>

            <div class="divider"></div>

            <p style="color: #6b7280; font-size: 14px;">
                Thank you for being part of the {{ config('app.name') }} community. We're here to help you keep your family organized and connected.
            </p>
        </div>

        <div class="footer">
            <p>&copy; {{ date('Y') }} {{ config('app.name') }}. All rights reserved.</p>
            <p>
                <a href="{{ config('app.url') }}">Visit Website</a>
                @if(isset($unsubscribeUrl))
                    &nbsp;|&nbsp;
                    <a href="{{ $unsubscribeUrl }}">Unsubscribe</a>
                @endif
            </p>
            <p style="margin-top: 16px; font-size: 11px; color: #9ca3af;">
                This email was sent to you because you signed up for {{ config('app.name') }}.
            </p>
        </div>
    </div>
</body>
</html>
