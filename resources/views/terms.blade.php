<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Terms of Service — BBSC Trainer Dashboard</title>
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

        <h1 class="text-3xl font-bold text-gray-900 mb-2">Terms of Service</h1>
        <p class="text-sm text-gray-500 mb-8">Last updated: {{ date('F j, Y') }}</p>

        <div class="prose max-w-none space-y-8 text-gray-700 leading-relaxed">

            <section>
                <h2 class="text-xl font-semibold text-gray-900 mb-3">1. Acceptance of Terms</h2>
                <p>
                    By registering for and using the BBSC Trainer Dashboard ("the Platform"), you agree to be
                    bound by these Terms of Service. If you do not agree to these terms, please do not use the Platform.
                    These terms apply to all trainers and users who access or use the Platform.
                </p>
            </section>

            <section>
                <h2 class="text-xl font-semibold text-gray-900 mb-3">2. Description of Service</h2>
                <p>
                    The BBSC Trainer Dashboard is an internal scheduling platform provided by BBSC for its registered
                    trainers. The Platform allows trainers to:
                </p>
                <ul class="list-disc list-inside mt-2 space-y-1 pl-4">
                    <li>Register and maintain a trainer account</li>
                    <li>Sign up for available training session days</li>
                    <li>View their upcoming schedule and assigned sessions</li>
                    <li>Access training plans uploaded by BBSC administrators</li>
                    <li>View their hours worked history</li>
                    <li>Receive SMS notifications regarding their assigned sessions</li>
                </ul>
            </section>

            <section>
                <h2 class="text-xl font-semibold text-gray-900 mb-3">3. Eligibility</h2>
                <p>
                    Use of the Platform is limited to individuals who are authorized trainers with BBSC.
                    By registering, you represent that you are a current BBSC trainer and that the information
                    you provide during registration is accurate and complete.
                </p>
            </section>

            <section>
                <h2 class="text-xl font-semibold text-gray-900 mb-3">4. Account Responsibilities</h2>
                <p>You are responsible for:</p>
                <ul class="list-disc list-inside mt-2 space-y-1 pl-4">
                    <li>Maintaining the confidentiality of your account password</li>
                    <li>All activity that occurs under your account</li>
                    <li>Keeping your contact information (especially your phone number) accurate and up to date</li>
                    <li>Notifying a BBSC administrator promptly if you believe your account has been compromised</li>
                </ul>
            </section>

            <section>
                <h2 class="text-xl font-semibold text-gray-900 mb-3">5. Scheduling and Availability</h2>
                <p>By signing up for a training session day, you acknowledge that:</p>
                <ul class="list-disc list-inside mt-2 space-y-1 pl-4">
                    <li>
                        Sign-up indicates your availability and willingness to work that day; final assignment is
                        determined by the BBSC scheduling system.
                    </li>
                    <li>
                        If assigned to a session, you are expected to attend unless you cancel through the Platform
                        in advance.
                    </li>
                    <li>
                        Cancellations are permitted through the Platform. Repeated last-minute cancellations may
                        affect your future assignment priority, at BBSC's discretion.
                    </li>
                    <li>
                        Session assignments are confirmed via SMS. You may confirm or cancel by replying YES or NO
                        to the SMS notification.
                    </li>
                </ul>
            </section>

            <section>
                <h2 class="text-xl font-semibold text-gray-900 mb-3">6. SMS Consent</h2>
                <p>
                    By providing your mobile phone number during registration, you expressly consent to receive
                    automated SMS text messages from BBSC via the BBSC Trainer Dashboard platform. These messages
                    are sent for scheduling and operational purposes only, including session assignment notifications
                    and scheduling reminders.
                </p>
                <p class="mt-3">
                    <strong>You understand that:</strong>
                </p>
                <ul class="list-disc list-inside mt-2 space-y-1 pl-4">
                    <li>Consent to receive SMS is not a condition of employment or service</li>
                    <li>Message frequency depends on how many sessions you are assigned to</li>
                    <li>Standard message and data rates from your carrier may apply</li>
                    <li>
                        You may opt out at any time by replying <strong>STOP</strong> to any message,
                        or by contacting your BBSC administrator
                    </li>
                    <li>
                        For help, reply <strong>HELP</strong> to any message or contact your BBSC administrator
                    </li>
                </ul>
            </section>

            <section>
                <h2 class="text-xl font-semibold text-gray-900 mb-3">7. Acceptable Use</h2>
                <p>You agree not to:</p>
                <ul class="list-disc list-inside mt-2 space-y-1 pl-4">
                    <li>Share your account credentials with others</li>
                    <li>Use the Platform for any purpose other than managing your BBSC trainer schedule</li>
                    <li>Attempt to access other trainers' information or accounts</li>
                    <li>Interfere with or disrupt the Platform or its servers</li>
                    <li>Provide false or misleading information during registration or use of the Platform</li>
                </ul>
            </section>

            <section>
                <h2 class="text-xl font-semibold text-gray-900 mb-3">8. Training Plans and Content</h2>
                <p>
                    Training plans and other materials made available through the Platform are the property of BBSC
                    and are provided solely for your use as a BBSC trainer. You may not distribute, share, or reproduce
                    these materials outside of your role as a BBSC trainer.
                </p>
            </section>

            <section>
                <h2 class="text-xl font-semibold text-gray-900 mb-3">9. Limitation of Liability</h2>
                <p>
                    The Platform is provided "as is" for internal scheduling purposes. BBSC makes no warranties,
                    express or implied, regarding the Platform's availability or accuracy. BBSC shall not be liable
                    for any damages arising from your use of or inability to use the Platform, including any scheduling
                    errors or missed notifications.
                </p>
            </section>

            <section>
                <h2 class="text-xl font-semibold text-gray-900 mb-3">10. Modifications</h2>
                <p>
                    BBSC reserves the right to modify these Terms of Service at any time. Updated terms will be
                    posted on this page with a revised effective date. Continued use of the Platform after changes
                    are posted constitutes your acceptance of the updated terms.
                </p>
            </section>

            <section>
                <h2 class="text-xl font-semibold text-gray-900 mb-3">11. Termination</h2>
                <p>
                    BBSC may suspend or terminate your access to the Platform at any time, for any reason,
                    including if you are no longer an active BBSC trainer or if you violate these Terms.
                    You may also request account deletion by contacting a BBSC administrator.
                </p>
            </section>

            <section>
                <h2 class="text-xl font-semibold text-gray-900 mb-3">12. Contact</h2>
                <p>
                    If you have questions about these Terms of Service, please contact your BBSC administrator.
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
