<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        body { font-family: Arial, sans-serif; background: #f3f4f6; margin: 0; padding: 0; }
        .wrapper { max-width: 560px; margin: 40px auto; background: #ffffff; border-radius: 8px; overflow: hidden; box-shadow: 0 1px 4px rgba(0,0,0,0.08); }
        .header { background: #111827; padding: 24px 32px; text-align: center; }
        .header img { height: 48px; width: auto; }
        .body { padding: 32px; color: #374151; }
        .body h1 { font-size: 20px; font-weight: 600; margin: 0 0 12px; color: #111827; }
        .body p { font-size: 15px; line-height: 1.6; margin: 0 0 16px; }
        .btn { display: inline-block; background: #111827; color: #ffffff !important; text-decoration: none; padding: 12px 28px; border-radius: 6px; font-size: 15px; font-weight: 600; margin: 8px 0 24px; }
        .footer { padding: 16px 32px; background: #f9fafb; border-top: 1px solid #e5e7eb; font-size: 12px; color: #9ca3af; text-align: center; }
        .url { word-break: break-all; font-size: 12px; color: #6b7280; }
    </style>
</head>
<body>
    <div class="wrapper">
        <div class="header">
            <img src="{{ asset('images/BBSClogo.png') }}" alt="BBSC">
        </div>
        <div class="body">
            <h1>You've been invited as an Admin</h1>
            <p>Hi {{ $user->name }},</p>
            <p>
                You've been added as an administrator on the <strong>BBSC Trainer Dashboard</strong>.
                Click the button below to set your password and access your account.
            </p>
            <a href="{{ $setupUrl }}" class="btn">Set Up My Account</a>
            <p>This link will expire in <strong>60 minutes</strong>. If you need a new link, use the "Forgot Password" option on the login page.</p>
            <p class="url">{{ $setupUrl }}</p>
        </div>
        <div class="footer">
            BBSC Trainer Dashboard &mdash; If you did not expect this invitation, you can ignore this email.
        </div>
    </div>
</body>
</html>
