<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Reset your password</title>
</head>
<body style="margin:0; padding:24px; background:#f5f0e8; font-family:Segoe UI, Tahoma, Geneva, Verdana, sans-serif; color:#1a1a1a;">
    <div style="max-width:640px; margin:0 auto; background:#ffffff; border:1px solid #e5e0d8; border-radius:24px; overflow:hidden;">
        <div style="padding:32px; background:linear-gradient(145deg, #1a3c34, #2d6b5e); color:#ffffff;">
            <p style="margin:0 0 12px; font-size:12px; letter-spacing:2px; text-transform:uppercase; color:rgba(255,255,255,.75);">
                GroqChatInterface
            </p>
            <h1 style="margin:0; font-size:32px; line-height:1.2;">Reset your password</h1>
        </div>

        <div style="padding:32px;">
            <p style="margin:0 0 16px;">Hi {{ $name }},</p>
            <p style="margin:0 0 24px; color:#5b6472; line-height:1.7;">
                We received a request to reset your password. Use the button below to choose a new one. This link expires in 60 minutes.
            </p>

            <p style="margin:0 0 24px;">
                <a
                    href="{{ $resetUrl }}"
                    style="display:inline-block; padding:14px 24px; background:#2d6b5e; color:#ffffff; text-decoration:none; border-radius:14px; font-weight:600;"
                >
                    Reset Password
                </a>
            </p>

            <p style="margin:0; color:#6b7280; line-height:1.7;">
                If you did not request this, you can safely ignore this email.
            </p>
        </div>
    </div>
</body>
</html>
