<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Access Verification</title>
</head>
<body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333; max-width: 600px; margin: 0 auto; padding: 20px;">
    <div style="background: linear-gradient(135deg, #9333ea 0%, #7c3aed 100%); padding: 30px; text-align: center; border-radius: 10px 10px 0 0;">
        <h1 style="color: white; margin: 0; font-size: 24px;">Family Ledger Backoffice</h1>
    </div>

    <div style="background: #f9fafb; padding: 30px; border: 1px solid #e5e7eb; border-top: none; border-radius: 0 0 10px 10px;">
        <p style="margin-top: 0;">Hello {{ $adminName }},</p>

        <p>You are requesting access to the Family Ledger Backoffice. Use the verification code below to confirm your identity:</p>

        <div style="background: white; border: 2px dashed #9333ea; border-radius: 10px; padding: 20px; text-align: center; margin: 25px 0;">
            <p style="margin: 0 0 10px 0; color: #6b7280; font-size: 14px;">Your Access Code</p>
            <p style="font-size: 36px; font-weight: bold; letter-spacing: 8px; color: #9333ea; margin: 0;">{{ $code }}</p>
        </div>

        <p style="color: #dc2626; font-size: 14px;"><strong>Important:</strong> This code will expire in 5 minutes.</p>

        <p>If you did not request backoffice access, please ignore this email. Someone may have entered your email address by mistake.</p>

        <hr style="border: none; border-top: 1px solid #e5e7eb; margin: 25px 0;">

        <p style="color: #6b7280; font-size: 12px; margin-bottom: 0;">
            This is an automated message from Family Ledger. Please do not reply to this email.
        </p>
    </div>
</body>
</html>
