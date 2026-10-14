<x-mail::message>

# Welcome to TrainerSync!

Your **{{ $clubName }}** account is ready. TrainerSync handles trainer scheduling, availability, SMS assignments, and payroll — all in one place.

To get started, set a password for your account:

<x-mail::button :url="$resetUrl" color="primary">
Set Your Password
</x-mail::button>

*This link expires in 60 minutes. If it expires, visit your club login page and use "Forgot password".*

---

## Your onboarding checklist

Here's everything to do once you're in:

**1. Upload your club logo & set brand colors**
Go to **Admin → Club Settings** to upload your logo and choose your primary and accent colors. These appear on the trainer portal and emails.

**2. Add your training season dates**
Go to **Admin → Training Days** to create your season schedule — dates, times, and locations for each training day.

**3. Invite trainers to register**
Share your club's login link (`{{ $clubUrl }}/register`) with your trainers. They register themselves and set their availability each week.

**4. Configure SMS notifications** *(optional)*
Go to **Admin → Settings** to add your Twilio credentials. Once set up, trainers receive an SMS when they're assigned to a training day and can confirm or cancel by reply.

**5. Set up your team rosters**
Go to **Admin → Teams** to add your teams and player rosters. These appear on the public-facing teams page.

---

— The TrainerSync Team

</x-mail::message>
