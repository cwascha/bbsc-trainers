<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Welcome to TrainerSync!</title>
    @vite(['resources/css/app.css'])
</head>
<body class="bg-gray-50 min-h-screen flex items-center justify-center p-6">
    <div class="max-w-md w-full bg-white rounded-xl shadow-lg p-10 text-center">
        <div class="w-16 h-16 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-5">
            <svg class="w-8 h-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
            </svg>
        </div>
        <h1 class="text-2xl font-bold text-gray-800 mb-2">You're all set!</h1>
        <p class="text-gray-500 text-sm mb-6">
            Your account is being set up. Check your email for a link to set your password
            and log in to your new TrainerSync dashboard.
        </p>
        <p class="text-gray-500 text-sm mb-6">
            It usually arrives within a minute. If you don't see it, check your spam folder.
        </p>
        <p class="text-xs text-gray-400">
            Questions? Email <a href="mailto:support@trainersync.app" class="underline">support@trainersync.app</a>
        </p>
    </div>
</body>
</html>
