<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        body { font-family: Arial, sans-serif; font-size: 15px; color: #333; margin: 0; padding: 0; background: #f4f4f4; }
        .wrapper { max-width: 600px; margin: 32px auto; background: #fff; border-radius: 8px; overflow: hidden; box-shadow: 0 1px 4px rgba(0,0,0,.08); }
        .header { background: #111827; padding: 20px 32px; }
        .header img { height: 12px; }
        .body { padding: 32px; }
        .greeting { font-size: 16px; margin-bottom: 24px; }
        .message { white-space: pre-line; line-height: 1.7; }
        .footer { padding: 20px 32px; background: #f9f9f9; border-top: 1px solid #eee; font-size: 12px; color: #999; }
    </style>
</head>
<body>
    <div class="wrapper">
        @php $emailLogo = $club?->logo_path ?: asset('images/BBSClogo.png'); @endphp
        <div class="header">
            <img src="{{ $emailLogo }}" alt="{{ $club?->name ?? config('app.name') }}">
        </div>
        <div class="body">
            <p class="greeting">Hi {{ $recipientName }},</p>
            <div class="message">{{ $emailBody }}</div>
        </div>
        <div class="footer">
            {{ $club?->name ?? config('app.name') }}
        </div>
    </div>
</body>
</html>
