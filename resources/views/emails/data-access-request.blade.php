<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Data Access Request</title>
</head>
<body style="font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; line-height: 1.6; color: #333; max-width: 600px; margin: 0 auto; padding: 20px;">
    <div style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); padding: 30px; border-radius: 10px 10px 0 0; text-align: center;">
        <h1 style="color: white; margin: 0; font-size: 24px;">Data Access Request</h1>
    </div>

    <div style="background: #ffffff; padding: 30px; border: 1px solid #e0e0e0; border-top: none; border-radius: 0 0 10px 10px;">
        <p style="font-size: 16px;">Hello {{ $ownerName }},</p>

        <p>A Family Ledger support administrator is requesting access to view your account data for support purposes.</p>

        <div style="background: #f8f9fa; border-left: 4px solid #667eea; padding: 15px; margin: 20px 0; border-radius: 4px;">
            <p style="margin: 0 0 10px 0;"><strong>Administrator:</strong> {{ $accessRequest->admin->name }}</p>
            <p style="margin: 0 0 10px 0;"><strong>Email:</strong> {{ $accessRequest->admin->email }}</p>
            @if($accessRequest->reason)
                <p style="margin: 0;"><strong>Reason:</strong> {{ $accessRequest->reason }}</p>
            @endif
        </div>

        <p style="color: #666; font-size: 14px;">
            <strong>What this means:</strong> If you approve, the administrator will be able to view your account information for a limited time (2 hours) to assist with your support request.
        </p>

        <div style="text-align: center; margin: 30px 0;">
            <a href="{{ $accessRequest->getApprovalUrl() }}" style="display: inline-block; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 14px 30px; text-decoration: none; border-radius: 6px; font-weight: 600; font-size: 16px;">
                Review Request
            </a>
        </div>

        <div style="background: #fff3cd; border: 1px solid #ffc107; padding: 15px; border-radius: 6px; margin: 20px 0;">
            <p style="margin: 0; color: #856404; font-size: 14px;">
                <strong>Important:</strong> This request will expire in 24 hours. If you did not contact Family Ledger support or do not recognize this request, you can safely ignore this email or deny the request.
            </p>
        </div>

        <hr style="border: none; border-top: 1px solid #e0e0e0; margin: 30px 0;">

        <p style="color: #999; font-size: 12px; text-align: center;">
            This email was sent by Family Ledger. If you have questions, please contact our support team.<br>
            Request ID: #{{ $accessRequest->id }} | Expires: {{ $accessRequest->expires_at->format('M j, Y g:i A') }}
        </p>
    </div>
</body>
</html>
