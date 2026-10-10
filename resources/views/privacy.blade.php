<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Privacy Policy — TrainerSync</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="font-sans text-gray-800 antialiased bg-gray-50">

    <header class="bg-white border-b border-gray-200 shadow-sm">
        <div class="max-w-4xl mx-auto px-6 py-4 flex items-center space-x-3">
            <a href="/" class="font-bold text-gray-900 text-lg">TrainerSync</a>
        </div>
    </header>

    <main class="max-w-4xl mx-auto px-6 py-10">

        <h1 class="text-3xl font-bold text-gray-900 mb-2">Privacy Policy</h1>
        <p class="text-sm text-gray-500 mb-8">Effective date: October 10, 2026</p>

        <div class="prose max-w-none space-y-8 text-gray-700 leading-relaxed">

            <section>
                <h2 class="text-xl font-semibold text-gray-900 mb-3">1. Introduction</h2>
                <p>
                    TrainerSync ("we," "us," or "our") operates a software platform for youth sports club trainer
                    operations. This Privacy Policy explains how we collect, use, share, and protect personal
                    information from two categories of users: <strong>Club Administrators</strong> (clubs and their
                    staff who subscribe to TrainerSync) and <strong>Trainers</strong> (individuals invited by a Club
                    to use the platform).
                </p>
                <p class="mt-3">
                    By using TrainerSync, you agree to the practices described in this policy.
                </p>
            </section>

            <section>
                <h2 class="text-xl font-semibold text-gray-900 mb-3">2. Information We Collect</h2>

                <h3 class="text-base font-semibold text-gray-800 mt-4 mb-2">From Club Administrators</h3>
                <ul class="list-disc list-inside space-y-1 pl-4">
                    <li><strong>Name and email address</strong> — used for account access and billing communications</li>
                    <li><strong>Club name</strong> — used to configure your Club's workspace</li>
                    <li><strong>Payment information</strong> — collected and stored by Stripe; we do not store card numbers directly</li>
                    <li><strong>Usage data</strong> — actions taken within the platform (sessions created, assignments run, etc.)</li>
                </ul>

                <h3 class="text-base font-semibold text-gray-800 mt-4 mb-2">From Trainers</h3>
                <ul class="list-disc list-inside space-y-1 pl-4">
                    <li><strong>Name and email address</strong> — used for account login and identification</li>
                    <li><strong>Mobile phone number</strong> — used to send SMS scheduling notifications</li>
                    <li><strong>Availability and session history</strong> — days signed up for and sessions worked</li>
                    <li><strong>Pay rate and Venmo handle</strong> — if provided by you or your Club Administrator, used for payroll reporting</li>
                    <li><strong>W-9 document</strong> — if uploaded, stored securely for your Club Administrator's records</li>
                </ul>

                <h3 class="text-base font-semibold text-gray-800 mt-4 mb-2">Automatically Collected</h3>
                <ul class="list-disc list-inside space-y-1 pl-4">
                    <li>Log data (IP address, browser type, pages visited) for security and debugging</li>
                    <li>Cookies and session tokens required for authentication</li>
                </ul>
            </section>

            <section>
                <h2 class="text-xl font-semibold text-gray-900 mb-3">3. How We Use Your Information</h2>
                <p>We use the information we collect to:</p>
                <ul class="list-disc list-inside mt-2 space-y-1 pl-4">
                    <li>Provide, maintain, and improve the TrainerSync platform</li>
                    <li>Manage your account and process subscription payments</li>
                    <li>Send you transactional emails (password resets, invitations, billing receipts)</li>
                    <li>Send SMS notifications on behalf of your Club for session assignments and scheduling</li>
                    <li>Generate payroll and attendance reports within your Club's workspace</li>
                    <li>Respond to support requests and troubleshoot issues</li>
                    <li>Comply with legal obligations</li>
                </ul>
                <p class="mt-3">
                    We do not use your information to train AI or machine learning models, and we do not sell
                    your personal information to third parties.
                </p>
            </section>

            <section>
                <h2 class="text-xl font-semibold text-gray-900 mb-3">4. SMS Messaging</h2>
                <p>
                    Trainers who provide a mobile phone number consent to receive SMS messages sent by TrainerSync
                    on behalf of their Club. These messages include session assignment notifications, confirmation
                    requests, and scheduling updates.
                </p>
                <ul class="list-disc list-inside mt-3 space-y-1 pl-4">
                    <li><strong>Message frequency:</strong> Varies based on your scheduling activity; typically one message per assigned session</li>
                    <li><strong>Carrier charges:</strong> Standard message and data rates from your carrier may apply</li>
                    <li><strong>Opt out:</strong> Reply <strong>STOP</strong> to any message at any time</li>
                    <li><strong>Help:</strong> Reply <strong>HELP</strong> or contact your Club Administrator</li>
                </ul>
                <p class="mt-3">
                    SMS messages are delivered via Twilio. Your phone number and message content are transmitted
                    to Twilio solely for delivery purposes.
                </p>
            </section>

            <section>
                <h2 class="text-xl font-semibold text-gray-900 mb-3">5. Information Sharing</h2>
                <p>We share personal information only in these limited circumstances:</p>
                <ul class="list-disc list-inside mt-2 space-y-1 pl-4">
                    <li>
                        <strong>Twilio</strong> — delivers SMS messages on our behalf. Phone numbers and message
                        content are shared only for this purpose. See
                        <a href="https://www.twilio.com/en-us/legal/privacy" class="text-indigo-600 underline" target="_blank">Twilio's Privacy Policy</a>.
                    </li>
                    <li>
                        <strong>Stripe</strong> — processes subscription payments for Club Administrators. Payment
                        card details are handled entirely by Stripe. See
                        <a href="https://stripe.com/privacy" class="text-indigo-600 underline" target="_blank">Stripe's Privacy Policy</a>.
                    </li>
                    <li>
                        <strong>Laravel Cloud</strong> — our hosting provider stores application data on secure
                        cloud infrastructure.
                    </li>
                    <li>
                        <strong>Club Administrators</strong> — your Club's administrators can view your name, email,
                        phone number, session history, hours, and payroll information within their workspace.
                    </li>
                    <li>
                        <strong>Legal requirements</strong> — we may disclose information if required by law, court
                        order, or government authority.
                    </li>
                </ul>
            </section>

            <section>
                <h2 class="text-xl font-semibold text-gray-900 mb-3">6. Data Security</h2>
                <p>
                    We implement industry-standard security measures including encrypted connections (HTTPS),
                    hashed passwords, and access controls. Only authorized TrainerSync personnel can access
                    platform infrastructure. However, no system is completely secure — please use a strong,
                    unique password and notify us immediately if you suspect unauthorized access to your account.
                </p>
            </section>

            <section>
                <h2 class="text-xl font-semibold text-gray-900 mb-3">7. Data Retention</h2>
                <p>
                    We retain Club and Trainer data for as long as a Club's subscription is active. After
                    cancellation, data is retained for up to 30 days to allow for export requests, then deleted.
                    Billing records may be retained longer as required by law.
                </p>
                <p class="mt-3">
                    Individual Trainers whose accounts are removed by a Club Administrator will have their data
                    deleted from that Club's workspace. If you are a Trainer and wish to request deletion of
                    your information, contact your Club Administrator or email us at
                    <a href="mailto:support@trainersync.app" class="text-indigo-600 underline">support@trainersync.app</a>.
                </p>
            </section>

            <section>
                <h2 class="text-xl font-semibold text-gray-900 mb-3">8. Your Rights</h2>
                <p>Depending on your location, you may have the right to:</p>
                <ul class="list-disc list-inside mt-2 space-y-1 pl-4">
                    <li>Access the personal information we hold about you</li>
                    <li>Correct inaccurate information (via your profile settings or by contacting us)</li>
                    <li>Request deletion of your personal information</li>
                    <li>Export your Club's data (Club Administrators)</li>
                    <li>Opt out of SMS notifications by replying STOP</li>
                    <li>Withdraw consent where processing is based on consent</li>
                </ul>
                <p class="mt-3">
                    To exercise these rights, contact us at
                    <a href="mailto:support@trainersync.app" class="text-indigo-600 underline">support@trainersync.app</a>.
                </p>
            </section>

            <section>
                <h2 class="text-xl font-semibold text-gray-900 mb-3">9. Children's Privacy</h2>
                <p>
                    TrainerSync is intended for use by adults (18 and older). We do not knowingly collect personal
                    information from children under 13. If you believe we have inadvertently collected such information,
                    please contact us immediately.
                </p>
            </section>

            <section>
                <h2 class="text-xl font-semibold text-gray-900 mb-3">10. Changes to This Policy</h2>
                <p>
                    We may update this Privacy Policy from time to time. We will notify active subscribers of
                    material changes via email before they take effect. The updated policy will also be posted
                    on this page with a revised effective date.
                </p>
            </section>

            <section>
                <h2 class="text-xl font-semibold text-gray-900 mb-3">11. Contact</h2>
                <p>
                    If you have questions or concerns about this Privacy Policy or how we handle your information,
                    contact us at:
                </p>
                <p class="mt-2">
                    <strong>TrainerSync</strong><br>
                    <a href="mailto:support@trainersync.app" class="text-indigo-600 underline">support@trainersync.app</a>
                </p>
            </section>

        </div>
    </main>

    <footer class="max-w-4xl mx-auto px-6 py-8 mt-6 border-t border-gray-200">
        <div class="flex items-center justify-between text-sm text-gray-500">
            <span>&copy; {{ date('Y') }} TrainerSync. All rights reserved.</span>
            <div class="space-x-4">
                <a href="{{ url('/privacy') }}" class="underline hover:text-gray-700">Privacy Policy</a>
                <a href="{{ url('/terms') }}" class="underline hover:text-gray-700">Terms of Service</a>
                <a href="{{ route('login') }}" class="underline hover:text-gray-700">Login</a>
            </div>
        </div>
    </footer>

</body>
</html>
