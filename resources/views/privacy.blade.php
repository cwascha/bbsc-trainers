<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Privacy Policy — BBSC Trainer Dashboard</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="font-sans text-gray-800 antialiased bg-gray-50">

    <!-- Header -->
    <header class="bg-white border-b border-gray-200 shadow-sm">
        <div class="max-w-4xl mx-auto px-6 py-4 flex items-center space-x-3">
            <a href="/">
                <img src="{{ asset('images/BBSClogo.png') }}" alt="BBSC Logo" style="height:40px;width:auto;">
            </a>
            <span class="font-bold text-gray-800 text-lg">BBSC Trainer Dashboard</span>
        </div>
    </header>

    <!-- Content -->
    <main class="max-w-4xl mx-auto px-6 py-10">

        <h1 class="text-3xl font-bold text-gray-900 mb-2">Privacy Policy</h1>
        <p class="text-sm text-gray-500 mb-8">Last updated: {{ date('F j, Y') }}</p>

        <div class="prose max-w-none space-y-8 text-gray-700 leading-relaxed">

            <section>
                <h2 class="text-xl font-semibold text-gray-900 mb-3">1. Introduction</h2>
                <p>
                    BBSC ("we," "us," or "our") operates the BBSC Trainer Dashboard, an internal web application
                    used to manage trainer scheduling for BBSC training sessions. This Privacy Policy explains how
                    we collect, use, and protect the personal information of registered trainers who use this platform.
                </p>
            </section>

            <section>
                <h2 class="text-xl font-semibold text-gray-900 mb-3">2. Information We Collect</h2>
                <p>When you register and use the BBSC Trainer Dashboard, we collect:</p>
                <ul class="list-disc list-inside mt-2 space-y-1 pl-4">
                    <li><strong>Name</strong> — used to identify you within the platform</li>
                    <li><strong>Email address</strong> — used for account login and notifications</li>
                    <li><strong>Mobile phone number</strong> — used to send you SMS notifications about your training schedule</li>
                    <li><strong>Availability selections</strong> — the training days you sign up for</li>
                    <li><strong>Session history</strong> — records of past training sessions you have worked</li>
                </ul>
            </section>

            <section>
                <h2 class="text-xl font-semibold text-gray-900 mb-3">3. How We Use Your Information</h2>
                <p>We use the information we collect solely for the following purposes:</p>
                <ul class="list-disc list-inside mt-2 space-y-1 pl-4">
                    <li>To manage your trainer account and login access</li>
                    <li>To schedule you for training sessions based on your availability</li>
                    <li>To send SMS notifications confirming your assignment to an upcoming training session</li>
                    <li>To allow you to confirm or cancel your assignment via SMS reply</li>
                    <li>To calculate and report trainer hours for payroll purposes</li>
                    <li>To provide access to training plans relevant to your assigned sessions</li>
                </ul>
            </section>

            <section>
                <h2 class="text-xl font-semibold text-gray-900 mb-3">4. SMS Messaging</h2>
                <p>
                    By providing your mobile phone number and registering as a trainer, you consent to receive
                    SMS text messages from BBSC regarding your training schedule. These messages may include:
                </p>
                <ul class="list-disc list-inside mt-2 space-y-1 pl-4">
                    <li>Notification that you have been assigned to an upcoming training session</li>
                    <li>Requests to confirm or cancel your assignment by replying YES or NO</li>
                    <li>Notifications if you have been reassigned to a session due to a cancellation</li>
                </ul>
                <p class="mt-3">
                    <strong>Message frequency:</strong> You will receive at most one SMS notification per training session you are assigned to.
                </p>
                <p class="mt-3">
                    <strong>To opt out:</strong> You may opt out of SMS notifications at any time by replying <strong>STOP</strong>
                    to any message you receive. You may also update your preferences by contacting your BBSC administrator.
                    Standard message and data rates may apply.
                </p>
            </section>

            <section>
                <h2 class="text-xl font-semibold text-gray-900 mb-3">5. Data Sharing</h2>
                <p>
                    We do not sell, rent, or share your personal information with third parties for marketing purposes.
                    Your information may be shared only in the following limited circumstances:
                </p>
                <ul class="list-disc list-inside mt-2 space-y-1 pl-4">
                    <li>
                        <strong>Twilio</strong> — We use Twilio to deliver SMS notifications. Your phone number
                        and message content are transmitted to Twilio solely for the purpose of delivering these messages.
                        Twilio's privacy policy is available at <a href="https://www.twilio.com/en-us/legal/privacy" class="text-indigo-600 underline" target="_blank">twilio.com/legal/privacy</a>.
                    </li>
                    <li>
                        <strong>BBSC administrators</strong> — BBSC staff with administrative access may view your
                        name, email, phone number, session history, and hours worked for scheduling and payroll purposes.
                    </li>
                </ul>
            </section>

            <section>
                <h2 class="text-xl font-semibold text-gray-900 mb-3">6. Data Security</h2>
                <p>
                    We implement reasonable security measures to protect your personal information from unauthorized
                    access, alteration, or disclosure. Your account is protected by a password that only you should know.
                    All data is stored on secured servers and access is restricted to authorized BBSC administrators.
                </p>
            </section>

            <section>
                <h2 class="text-xl font-semibold text-gray-900 mb-3">7. Data Retention</h2>
                <p>
                    We retain your personal information for as long as you maintain an active trainer account with BBSC,
                    or as long as necessary to fulfill the purposes described in this policy, including payroll recordkeeping.
                    You may request deletion of your account by contacting a BBSC administrator.
                </p>
            </section>

            <section>
                <h2 class="text-xl font-semibold text-gray-900 mb-3">8. Your Rights</h2>
                <p>You have the right to:</p>
                <ul class="list-disc list-inside mt-2 space-y-1 pl-4">
                    <li>Access the personal information we hold about you</li>
                    <li>Request correction of inaccurate information via your profile settings</li>
                    <li>Request deletion of your account and associated data</li>
                    <li>Opt out of SMS notifications at any time by replying STOP</li>
                </ul>
                <p class="mt-3">To exercise any of these rights, contact your BBSC administrator.</p>
            </section>

            <section>
                <h2 class="text-xl font-semibold text-gray-900 mb-3">9. Changes to This Policy</h2>
                <p>
                    We may update this Privacy Policy from time to time. Changes will be posted on this page
                    with an updated effective date. Continued use of the BBSC Trainer Dashboard after changes
                    are posted constitutes your acceptance of the updated policy.
                </p>
            </section>

            <section>
                <h2 class="text-xl font-semibold text-gray-900 mb-3">10. Contact</h2>
                <p>
                    If you have any questions about this Privacy Policy or your personal information,
                    please contact your BBSC administrator.
                </p>
            </section>

        </div>
    </main>

    <!-- Footer -->
    <footer class="max-w-4xl mx-auto px-6 py-8 mt-6 border-t border-gray-200">
        <div class="flex items-center justify-between text-sm text-gray-500">
            <span>&copy; {{ date('Y') }} BBSC. All rights reserved.</span>
            <div class="space-x-4">
                <a href="{{ url('/privacy') }}" class="underline hover:text-gray-700">Privacy Policy</a>
                <a href="{{ url('/terms') }}" class="underline hover:text-gray-700">Terms of Service</a>
                <a href="{{ route('login') }}" class="underline hover:text-gray-700">Login</a>
            </div>
        </div>
    </footer>

</body>
</html>
