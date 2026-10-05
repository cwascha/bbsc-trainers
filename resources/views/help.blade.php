<x-app-layout>
<div x-data="{
    tab: '{{ Auth::user()->isAdmin() ? 'admin' : 'trainer' }}',
    section: null,
    open(id) {
        this.section = (this.section === id) ? null : id;
        if (this.section) {
            this.$nextTick(() => {
                document.getElementById(id)?.scrollIntoView({ behavior: 'smooth', block: 'start' });
            });
        }
    }
}" class="max-w-5xl mx-auto px-4 py-8">

    <h1 class="text-3xl font-bold text-gray-800 mb-2">Help &amp; User Guide</h1>
    <p class="text-gray-500 mb-6">Everything you need to know about using {{ $currentClub->name ?? config('app.name') }}.</p>

    {{-- Tab switcher --}}
    <div class="flex gap-1 mb-8 border-b border-gray-200">
        @if(Auth::user()->isAdmin())
        <button @click="tab = 'admin'"
                :class="tab === 'admin' ? 'border-b-2 border-gray-800 text-gray-800 font-semibold' : 'text-gray-500 hover:text-gray-700'"
                class="px-4 py-2 text-sm transition">
            Admin Guide
        </button>
        @endif
        <button @click="tab = 'trainer'"
                :class="tab === 'trainer' ? 'border-b-2 border-gray-800 text-gray-800 font-semibold' : 'text-gray-500 hover:text-gray-700'"
                class="px-4 py-2 text-sm transition">
            Trainer Guide
        </button>
    </div>

    {{-- ══════════════════════════════════════════════════════════════════════
         ADMIN GUIDE
    ══════════════════════════════════════════════════════════════════════ --}}
    @if(Auth::user()->isAdmin())
    <div x-show="tab === 'admin'" x-cloak class="space-y-3">

        @php
        $adminSections = [
            'a-overview' => [
                'title' => 'Dashboard Overview',
                'icon'  => 'M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6',
                'body'  => <<<HTML
<p>The Admin Dashboard gives you a real-time snapshot of your program:</p>
<ul>
    <li><strong>Upcoming sessions</strong> — the next training day, how many spots are filled, and confirmed vs. pending trainers.</li>
    <li><strong>Quick stats</strong> — total trainers, sessions this season, and outstanding payroll.</li>
    <li><strong>Recent activity</strong> — the latest assignments and confirmations.</li>
</ul>
<p>Use the navigation bar at the top to jump to any section. On smaller screens it scrolls horizontally.</p>
HTML,
            ],
            'a-seasons' => [
                'title' => 'Seasons &amp; Training Days',
                'icon'  => 'M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z',
                'body'  => <<<HTML
<p>Before trainers can sign up for availability, you need to create the training days for the season.</p>
<h4>Creating a season</h4>
<ol>
    <li>Go to <strong>Admin → Seasons</strong>.</li>
    <li>Fill in the season name (e.g. <em>Spring 2027</em>), year, and status, then click <strong>Create Season</strong>.</li>
    <li>Click <strong>Manage Days</strong> on the new season card.</li>
</ol>
<h4>Bulk generating training days</h4>
<ol>
    <li>Expand <strong>Bulk Generate Training Days</strong>.</li>
    <li>Set the <strong>First Saturday</strong> date, <strong>number of weekends</strong>, and any <strong>skip dates</strong> (comma-separated, e.g. holiday weekends).</li>
    <li>Set Saturday start/end times, max spots, and an optional program label (e.g. <em>Sparks</em>).</li>
    <li>Check <strong>include Sunday</strong> and fill in Sunday times if your program runs two days per weekend.</li>
    <li>Click <strong>Generate Days</strong> — existing dates are skipped automatically, so it's safe to run again.</li>
</ol>
<h4>Adding or editing individual days</h4>
<ul>
    <li>Use <strong>Add Single Training Day</strong> at the bottom for one-off sessions.</li>
    <li>Click <strong>Edit</strong> on any row to open an inline edit form — change the date, times, spots, or program label and click <strong>Save</strong>.</li>
    <li>Click <strong>Delete</strong> to remove a day (this also removes any trainer availability linked to it).</li>
</ul>
<p class="tip">💡 Weekend Number groups days together for the assignment algorithm and payroll. Days on the same weekend share the same number (e.g. Saturday and Sunday of weekend 3 both get <em>Weekend 3</em>).</p>
HTML,
            ],
            'a-sessions' => [
                'title' => 'Sessions &amp; Assignments',
                'icon'  => 'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2',
                'body'  => <<<HTML
<p>The <strong>Sessions</strong> page shows every training day with the trainers signed up for it.</p>
<h4>Automated weekly assignment</h4>
<p>Every Thursday at 8 AM the system automatically assigns trainers to the upcoming weekend:</p>
<ol>
    <li>It processes Saturday first, then Sunday.</li>
    <li>Trainers who haven't been assigned anywhere that weekend yet (Bucket A) are prioritised, sorted by when they signed up.</li>
    <li>Once Bucket A is exhausted, trainers already assigned to the other day that weekend (Bucket B) fill remaining spots.</li>
    <li>Each assigned trainer receives an SMS with a confirmation link.</li>
</ol>
<h4>Running assignments manually</h4>
<p>Click <strong>Run Assignment</strong> on the Sessions page to trigger the algorithm immediately — useful for testing or if Thursday's run needs to be re-done.</p>
<h4>Managing individual trainers on a session</h4>
<ul>
    <li><strong>Add a trainer</strong> — search by name and click Add. An SMS confirmation is sent automatically.</li>
    <li><strong>Confirm a trainer</strong> — manually mark them confirmed if they told you verbally.</li>
    <li><strong>Remove a trainer</strong> — removes them from the session and triggers a reassignment attempt for that day.</li>
    <li><strong>Mark no-show</strong> — records that a trainer was assigned but didn't attend (affects payroll).</li>
</ul>
<h4>Attendance tracking</h4>
<p>After a session, use the <strong>Mark Worked / No Show</strong> controls to record actual attendance. This drives the payroll calculations.</p>
HTML,
            ],
            'a-trainers' => [
                'title' => 'Managing Trainers',
                'icon'  => 'M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z',
                'body'  => <<<HTML
<h4>Adding a trainer manually</h4>
<ol>
    <li>Go to <strong>Admin → Trainers</strong>.</li>
    <li>Fill in name, email, phone, and pay rate, then click <strong>Add Trainer</strong>.</li>
    <li>The trainer receives a "Forgot Password" email so they can set their own password and log in.</li>
</ol>
<h4>Importing trainers from CSV</h4>
<ol>
    <li>Click <strong>Download example CSV</strong> to get a template.</li>
    <li>Fill it in with your trainer list. Required column: <strong>Email</strong>. Optional: First Name, Last Name, Phone, Venmo, Pay Rate.</li>
    <li>Upload the file and click <strong>Import</strong>.</li>
    <li>Existing trainers are updated (pay rate, Venmo, phone) if those columns are present. New trainers are created.</li>
</ol>
<h4>Editing a trainer</h4>
<ul>
    <li>Click the trainer's name to expand their row, or use the edit icons to update pay rate, lead trainer status, or contact details.</li>
    <li><strong>Lead Trainer</strong> flag marks a trainer as a lead — useful for filtering and reporting.</li>
    <li>To remove a trainer, click <strong>Delete</strong> — this is irreversible and removes all their availability records.</li>
</ul>
<h4>W-9 tracking</h4>
<p>Trainers can upload their W-9 from their profile. The Trainers page shows a ✓ or pending icon per trainer. You can download a trainer's W-9 or mark it as received from the admin panel.</p>
HTML,
            ],
            'a-sms' => [
                'title' => 'SMS Notifications',
                'icon'  => 'M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z',
                'body'  => <<<HTML
<h4>Bulk SMS (send to all trainers)</h4>
<ol>
    <li>Go to <strong>Admin → SMS</strong>.</li>
    <li>Type your message and click <strong>Send to All Trainers</strong>.</li>
    <li>Messages are queued and sent in the background — the page responds immediately while sends happen behind the scenes.</li>
</ol>
<h4>Send to a specific session</h4>
<p>On the <strong>Sessions</strong> page, each training day has a <strong>Send SMS to this session</strong> button. This sends only to trainers assigned to that day.</p>
<h4>Trainer replies</h4>
<p>When a trainer replies <strong>YES</strong> to their assignment SMS, they are automatically confirmed. A reply of <strong>NO</strong> or <strong>CANCEL</strong> cancels their assignment and the system attempts to reassign their spot to the next available trainer.</p>
<h4>SMS logs</h4>
<p>Every message sent through the platform is logged under <strong>SMS Logs</strong>. You can see the message, recipient, timestamp, and which session it was related to.</p>
HTML,
            ],
            'a-payroll' => [
                'title' => 'Payroll &amp; Reports',
                'icon'  => 'M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z',
                'body'  => <<<HTML
<p>The <strong>Payroll</strong> page calculates what each trainer is owed based on sessions worked and their hourly rate.</p>
<h4>How earnings are calculated</h4>
<ul>
    <li>Each worked session contributes <em>session hours × pay rate</em>.</li>
    <li>Hours are pulled from the session's start/end times unless manually overridden.</li>
    <li>Planning hours (entered by trainers or admins) are added separately.</li>
</ul>
<h4>Manual adjustments</h4>
<ul>
    <li><strong>Override hours</strong> — enter a custom number of hours for a trainer on a specific session.</li>
    <li><strong>Add manual payment</strong> — record a one-off payment (bonus, reimbursement) outside the session structure.</li>
</ul>
<h4>Marking as paid</h4>
<p>Once you've paid a trainer (via Venmo, check, etc.), click <strong>Mark Paid</strong>. This records the payment date and zeroes their outstanding balance. Click <strong>Clear Payment</strong> to undo.</p>
<h4>Exporting</h4>
<p>Click <strong>Export CSV</strong> to download the full payroll report — useful for your accounting records.</p>
HTML,
            ],
            'a-plans' => [
                'title' => 'Training Plans',
                'icon'  => 'M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z',
                'body'  => <<<HTML
<p>Training Plans are PDF documents attached to a weekend number. When a trainer is assigned to weekend 3, they automatically see and can download the weekend 3 plan.</p>
<h4>Uploading a plan</h4>
<ol>
    <li>Go to <strong>Admin → Plans</strong>.</li>
    <li>Choose the weekend number, upload the PDF, and click <strong>Upload</strong>.</li>
    <li>Trainers assigned to that weekend will see it immediately in their <strong>Training Plans</strong> tab.</li>
</ol>
<h4>SMS links</h4>
<p>Assignment SMS messages include a signed link to the training plan. The link works without logging in — it's valid for a limited time and tied to that specific trainer and weekend.</p>
HTML,
            ],
            'a-teams' => [
                'title' => 'Teams &amp; Rosters',
                'icon'  => 'M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z',
                'body'  => <<<HTML
<p>The Teams section manages the player rosters that trainers work with each weekend.</p>
<h4>Importing rosters</h4>
<ol>
    <li>Go to <strong>Admin → Teams</strong>.</li>
    <li>Upload the roster spreadsheet (Excel format). The importer reads the Kindergarten Girls, Kindergarten Boys, and other program sheets automatically.</li>
    <li>Existing players are updated; new players are created.</li>
</ol>
<h4>Public roster page</h4>
<p>Each team has a public URL that parents can access without logging in. Share the link with families so they can check their child's team and session details.</p>
HTML,
            ],
            'a-branding' => [
                'title' => 'Branding',
                'icon'  => 'M7 21a4 4 0 01-4-4V5a2 2 0 012-2h4a2 2 0 012 2v12a4 4 0 01-4 4zm0 0h12a2 2 0 002-2v-4a2 2 0 00-2-2h-2.343M11 7.343l1.657-1.657a2 2 0 012.828 0l2.829 2.829a2 2 0 010 2.828l-8.486 8.485M7 17h.01',
                'body'  => <<<HTML
<p>Customize the platform to match your club's identity.</p>
<h4>Uploading a logo</h4>
<ol>
    <li>Go to <strong>Admin → Branding</strong>.</li>
    <li>Click <strong>Choose File</strong> under Logo and select a PNG, JPG, SVG, or WebP (max 2 MB).</li>
    <li>Click <strong>Save Branding</strong>. The logo appears in the navbar for all users immediately.</li>
</ol>
<p>Logos are stored in the database, so they survive server restarts and redeployments without any file storage configuration.</p>
<h4>Colors</h4>
<ul>
    <li><strong>Primary Color</strong> — the navbar background color. Use your club's main brand color.</li>
    <li><strong>Accent Color</strong> — used for buttons and highlights.</li>
</ul>
<p>A live navbar preview updates as you pick colors, so you can see exactly how it will look before saving.</p>
HTML,
            ],
            'a-admins' => [
                'title' => 'Admin Users &amp; Settings',
                'icon'  => 'M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z M15 12a3 3 0 11-6 0 3 3 0 016 0z',
                'body'  => <<<HTML
<h4>Adding admin users</h4>
<ol>
    <li>Go to <strong>Admin → Settings</strong>.</li>
    <li>Enter the email of an existing trainer and click <strong>Make Admin</strong>.</li>
    <li>That person will see the Admin Panel button after their next login.</li>
</ol>
<h4>Removing an admin</h4>
<p>Click <strong>Remove Admin</strong> next to any admin user. Their account stays active as a regular trainer; they just lose admin access.</p>
<h4>Club name</h4>
<p>Your club name is shown in the navbar and in all outgoing SMS messages. Update it under <strong>Admin → Branding</strong>.</p>
HTML,
            ],
        ];
        @endphp

        @foreach($adminSections as $id => $sec)
        <div class="bg-white rounded-lg shadow overflow-hidden">
            <button @click="open('{{ $id }}')"
                    class="w-full flex items-center gap-3 px-5 py-4 text-left hover:bg-gray-50 transition">
                <svg class="w-5 h-5 text-gray-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $sec['icon'] }}"/>
                </svg>
                <span class="font-semibold text-gray-800">{{ $sec['title'] }}</span>
                <svg class="w-4 h-4 text-gray-400 ml-auto transition-transform"
                     :class="section === '{{ $id }}' ? 'rotate-180' : ''"
                     fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                </svg>
            </button>
            <div id="{{ $id }}" x-show="section === '{{ $id }}'" x-collapse
                 class="px-5 pb-5 pt-1 border-t border-gray-100 prose prose-sm max-w-none text-gray-700">
                {!! $sec['body'] !!}
            </div>
        </div>
        @endforeach

    </div>
    @endif

    {{-- ══════════════════════════════════════════════════════════════════════
         TRAINER GUIDE
    ══════════════════════════════════════════════════════════════════════ --}}
    <div x-show="tab === 'trainer'" x-cloak class="space-y-3">

        @php
        $trainerSections = [
            't-dashboard' => [
                'title' => 'Your Dashboard',
                'icon'  => 'M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6',
                'body'  => <<<HTML
<p>When you log in you land on your Dashboard. It shows:</p>
<ul>
    <li><strong>Your upcoming sessions</strong> — dates you're assigned to, with times and location.</li>
    <li><strong>Your availability status</strong> — which upcoming weekends you've signed up for.</li>
    <li><strong>Quick links</strong> to your schedule, training plans, and hours.</li>
</ul>
<p>Use the navigation bar at the top to move between sections.</p>
HTML,
            ],
            't-availability' => [
                'title' => 'Signing Up for Availability',
                'icon'  => 'M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z',
                'body'  => <<<HTML
<p>Availability is how you tell the admin which sessions you're able to work.</p>
<h4>How to sign up</h4>
<ol>
    <li>Go to <strong>Schedule</strong> in the top navigation.</li>
    <li>You'll see each upcoming training day listed by date.</li>
    <li>Click <strong>I'm Available</strong> on any day you can work.</li>
    <li>To remove your availability, click <strong>Remove</strong> on a day you've already signed up for.</li>
</ol>
<h4>What happens next</h4>
<p>Every Thursday at 8 AM, the system assigns trainers to the upcoming weekend's sessions based on availability. You'll receive an <strong>SMS confirmation</strong> on your registered phone number.</p>
<p class="tip">💡 Sign up for availability as early as possible — trainers who signed up first are prioritised in the assignment algorithm.</p>
HTML,
            ],
            't-confirmation' => [
                'title' => 'Confirming or Cancelling via SMS',
                'icon'  => 'M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z',
                'body'  => <<<HTML
<p>When you're assigned to a session, you'll receive an SMS from {{ $currentClub->name ?? 'the club' }}. The message will include the date, time, and a link to download your training plan.</p>
<h4>Confirming</h4>
<p>Reply <strong>YES</strong> to the SMS to confirm you'll be there. Your status will update to <em>Confirmed</em>.</p>
<h4>Cancelling</h4>
<p>Reply <strong>NO</strong> or <strong>CANCEL</strong> to cancel your assignment. The system will automatically attempt to find a replacement from the available trainer list.</p>
<p>You can also cancel from the <strong>Schedule</strong> page on the website if you prefer not to reply by text.</p>
<p class="tip">⚠️ Please reply as soon as possible so the admin has time to find a replacement if needed.</p>
HTML,
            ],
            't-schedule' => [
                'title' => 'Viewing Your Schedule',
                'icon'  => 'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2',
                'body'  => <<<HTML
<p>The <strong>Schedule</strong> page shows all upcoming training days and your availability or assignment status for each one.</p>
<ul>
    <li><strong>Available</strong> (blue) — you've signed up and are waiting to be assigned.</li>
    <li><strong>Assigned</strong> (yellow) — you've been assigned but haven't confirmed yet. Reply YES to your SMS or confirm on this page.</li>
    <li><strong>Confirmed</strong> (green) — you're confirmed for this session.</li>
    <li><strong>Cancelled</strong> (gray) — your assignment was cancelled.</li>
</ul>
<p>Past sessions appear at the bottom of the list so you can see your history.</p>
HTML,
            ],
            't-plans' => [
                'title' => 'Accessing Training Plans',
                'icon'  => 'M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z',
                'body'  => <<<HTML
<p>Training plans are PDF documents uploaded by the admin for each weekend. You get access to the plan for any weekend you're assigned to.</p>
<h4>Viewing your plan</h4>
<ol>
    <li>Go to <strong>Training Plans</strong> in the navigation.</li>
    <li>Your current and upcoming plans are listed there. Click <strong>Download</strong> to open the PDF.</li>
</ol>
<h4>Via SMS</h4>
<p>Your assignment SMS also includes a direct link to your training plan. You can open it without logging in — just tap the link in the text message.</p>
HTML,
            ],
            't-hours' => [
                'title' => 'Tracking Your Hours',
                'icon'  => 'M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z',
                'body'  => <<<HTML
<p>The <strong>My Hours</strong> page shows the sessions you've worked and your estimated earnings.</p>
<h4>Session hours</h4>
<p>Hours are automatically calculated from each session's start and end time once you've been marked as worked by the admin.</p>
<h4>Planning hours</h4>
<p>If you spend time outside of sessions on planning (reviewing curriculum, preparing materials), you can log those hours manually:</p>
<ol>
    <li>Go to <strong>My Hours</strong>.</li>
    <li>Enter your planning hours for the relevant weekend and click <strong>Save</strong>.</li>
</ol>
<p>Planning hours are added to your session hours when calculating your total pay.</p>
HTML,
            ],
            't-documents' => [
                'title' => 'Documents',
                'icon'  => 'M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z',
                'body'  => <<<HTML
<p>The <strong>Documents</strong> section contains any files the admin has shared with all trainers — contracts, handbooks, policies, or other reference material.</p>
<ul>
    <li>Click <strong>Download</strong> to save a file.</li>
    <li>Click <strong>View</strong> to open it in the browser.</li>
</ul>
<p>Documents are shared club-wide — you'll see anything the admin has uploaded for trainers.</p>
HTML,
            ],
            't-w9' => [
                'title' => 'W-9 &amp; Profile',
                'icon'  => 'M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z',
                'body'  => <<<HTML
<h4>Updating your profile</h4>
<ol>
    <li>Click your name in the top-right corner and select <strong>Profile</strong>.</li>
    <li>Update your name, phone number, Venmo handle, or password.</li>
    <li>Click <strong>Save</strong>.</li>
</ol>
<h4>Uploading your W-9</h4>
<p>If the club requires a W-9 for tax purposes:</p>
<ol>
    <li>Go to your <strong>Profile</strong> page.</li>
    <li>Click <strong>Upload W-9</strong> and select your completed PDF.</li>
    <li>The admin will be notified and can download it from the Trainers page.</li>
</ol>
<p>You can replace your W-9 at any time by uploading a new file.</p>
HTML,
            ],
        ];
        @endphp

        @foreach($trainerSections as $id => $sec)
        <div class="bg-white rounded-lg shadow overflow-hidden">
            <button @click="open('{{ $id }}')"
                    class="w-full flex items-center gap-3 px-5 py-4 text-left hover:bg-gray-50 transition">
                <svg class="w-5 h-5 text-gray-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $sec['icon'] }}"/>
                </svg>
                <span class="font-semibold text-gray-800">{{ $sec['title'] }}</span>
                <svg class="w-4 h-4 text-gray-400 ml-auto transition-transform"
                     :class="section === '{{ $id }}' ? 'rotate-180' : ''"
                     fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                </svg>
            </button>
            <div id="{{ $id }}" x-show="section === '{{ $id }}'" x-collapse
                 class="px-5 pb-5 pt-1 border-t border-gray-100 prose prose-sm max-w-none text-gray-700">
                {!! $sec['body'] !!}
            </div>
        </div>
        @endforeach

    </div>

    {{-- Contact footer --}}
    <div class="mt-8 p-4 bg-gray-50 rounded-lg text-center text-sm text-gray-500">
        Still have questions? Contact your club admin directly.
    </div>

</div>

<style>
.prose h4 { font-weight: 600; margin-top: 1rem; margin-bottom: 0.25rem; color: #1f2937; }
.prose ol, .prose ul { padding-left: 1.25rem; margin: 0.5rem 0; }
.prose li { margin: 0.25rem 0; }
.prose p { margin: 0.5rem 0; }
.prose strong { color: #111827; }
.prose .tip { background: #f0fdf4; border-left: 3px solid #22c55e; padding: 0.5rem 0.75rem; border-radius: 0 0.25rem 0.25rem 0; font-size: 0.8rem; }
</style>
</x-app-layout>
