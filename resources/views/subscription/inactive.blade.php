<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Subscription Inactive — {{ $club->name }}</title>
    @vite(['resources/css/app.css'])
</head>
<body class="min-h-screen bg-gray-50 flex items-center justify-center p-6">
    <div class="max-w-md w-full bg-white rounded-xl shadow-lg p-8 text-center">
        <div class="w-14 h-14 bg-yellow-100 rounded-full flex items-center justify-center mx-auto mb-4">
            <svg class="w-7 h-7 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M12 9v2m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/>
            </svg>
        </div>
        <h1 class="text-xl font-bold text-gray-800 mb-2">Subscription Inactive</h1>
        <p class="text-gray-500 text-sm mb-6">
            Your subscription for <strong>{{ $club->name }}</strong> is currently
            <strong>{{ $club->subscription_status }}</strong>.
            Please contact your administrator or renew your subscription to regain access.
        </p>
        <p class="text-xs text-gray-400">
            If you believe this is an error, contact
            <a href="mailto:support@pitchside.app" class="underline">support@pitchside.app</a>.
        </p>
    </div>
</body>
</html>
