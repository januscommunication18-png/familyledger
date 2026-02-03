<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Recovery Code {{ $actionType === 'set' ? 'Set' : 'Updated' }}</title>
</head>
<body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333; max-width: 600px; margin: 0 auto; padding: 20px;">
    <div style="background: linear-gradient(135deg, #9333ea 0%, #7c3aed 100%); padding: 30px; text-align: center; border-radius: 10px 10px 0 0;">
        <h1 style="color: white; margin: 0; font-size: 24px;">Family Ledger</h1>
    </div>

    <div style="background: #f9fafb; padding: 30px; border: 1px solid #e5e7eb; border-top: none; border-radius: 0 0 10px 10px;">
        <p style="margin-top: 0;">Hello {{ $userName }},</p>

        <p>Your account recovery code has been successfully {{ $actionType === 'set' ? 'set' : 'updated' }}.</p>

        <div style="background: linear-gradient(135deg, #f3e8ff 0%, #faf5ff 100%); border: 2px solid #9333ea; border-radius: 10px; padding: 25px; margin: 25px 0; text-align: center;">
            <p style="margin: 0 0 10px 0; color: #6b7280; font-size: 14px;">Your Recovery Code</p>
            <p style="font-size: 28px; font-weight: bold; letter-spacing: 4px; color: #9333ea; margin: 0; font-family: 'Courier New', monospace;">
                {{ substr($recoveryCode, 0, 4) }} {{ substr($recoveryCode, 4, 4) }} {{ substr($recoveryCode, 8, 4) }} {{ substr($recoveryCode, 12, 4) }}
            </p>
            <p style="margin: 15px 0 0 0; color: #6b7280; font-size: 12px;">
                Save this code in a secure location
            </p>
        </div>

        <div style="background: white; border: 1px solid #e5e7eb; border-radius: 10px; padding: 20px; margin: 25px 0;">
            <p style="margin: 0 0 8px 0; font-weight: 600; color: #374151;">Important Security Information</p>
            <p style="margin: 0; color: #6b7280; font-size: 14px;">
                Store your recovery code in a safe place. You may need to provide it to our support team to verify your identity if you ever lose access to your account.
            </p>
        </div>

        <div style="background: #fef2f2; border: 1px solid #fecaca; border-radius: 10px; padding: 15px; margin: 20px 0;">
            <p style="margin: 0; color: #dc2626; font-size: 14px;">
                <strong>Security Notice:</strong> If you did not make this change, please contact support immediately and reset your password. Someone may have unauthorized access to your account.
            </p>
        </div>

        <hr style="border: none; border-top: 1px solid #e5e7eb; margin: 25px 0;">

        <p style="color: #6b7280; font-size: 12px; margin-bottom: 0;">
            This is an automated message from Family Ledger. Please do not reply to this email.
        </p>
    </div>
</body>
</html>
