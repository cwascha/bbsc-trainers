<?php

namespace App\Http\Controllers\Webhooks;

use App\Http\Controllers\Controller;
use App\Models\Club;
use App\Models\User;
use Illuminate\Http\Request;
use App\Notifications\NewClubWelcome;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Stripe\Exception\SignatureVerificationException;
use Stripe\Stripe;
use Stripe\Webhook;

class StripeController extends Controller
{
    public function handle(Request $request)
    {
        Stripe::setApiKey(config('services.stripe.secret'));

        $payload = $request->getContent();
        $sig     = $request->header('Stripe-Signature');
        $secret  = config('services.stripe.webhook_secret');

        Log::info('Stripe webhook received', [
            'secret_set' => !empty($secret),
            'sig_header' => $sig ? substr($sig, 0, 30) . '...' : 'MISSING',
        ]);

        try {
            $event = Webhook::constructEvent($payload, $sig, $secret);
        } catch (SignatureVerificationException $e) {
            Log::warning('Stripe webhook signature mismatch: ' . $e->getMessage());
            return response('Signature mismatch: ' . $e->getMessage(), 400);
        } catch (\Exception $e) {
            Log::error('Stripe webhook error: ' . $e->getMessage());
            return response('Webhook error: ' . $e->getMessage(), 400);
        }

        Log::info('Stripe webhook event received', [
            'type'     => $event->type,
            'id'       => $event->id,
            'data_obj' => json_encode($event->data->object ?? null),
        ]);

        try {
            match ($event->type) {
                'checkout.session.completed'    => $this->handleCheckoutCompleted($event->data->object),
                'customer.subscription.updated' => $this->handleSubscriptionUpdated($event->data->object),
                'customer.subscription.deleted' => $this->handleSubscriptionDeleted($event->data->object),
                default                         => Log::warning('Stripe webhook: unhandled event type: ' . $event->type),
            };
        } catch (\Throwable $e) {
            Log::error('Stripe webhook handler exception', [
                'event_type' => $event->type,
                'error'      => $e->getMessage(),
                'trace'      => $e->getTraceAsString(),
            ]);
        }

        return response('OK', 200);
    }

    private function handleCheckoutCompleted(object $session): void
    {
        $meta = $session->metadata ? $session->metadata->toArray() : [];

        $clubName   = $meta['club_name']   ?? 'New Club';
        $adminName  = $meta['admin_name']  ?? 'Admin';
        $adminEmail = $meta['admin_email'] ?? null;

        if (! $adminEmail) {
            Log::error('Stripe checkout.session.completed: missing admin_email in metadata', [
                'session_id'       => $session->id ?? 'no-id',
                'raw_metadata'     => json_encode($session->metadata ?? null),
                'session_keys'     => is_object($session) ? array_keys((array) $session) : 'not-object',
                'customer_details' => json_encode($session->customer_details ?? null),
            ]);
            return;
        }

        // Generate a URL-safe slug from the club name
        $baseSlug = Str::slug($clubName);
        $slug     = $baseSlug;
        $i        = 2;
        while (Club::where('slug', $slug)->exists()) {
            $slug = $baseSlug . '-' . $i++;
        }

        $trialDays = (int) config('services.stripe.trial_days', 0);

        $club = Club::create([
            'name'                   => $clubName,
            'slug'                   => $slug,
            'primary_color'          => '#111827',
            'accent_color'           => '#3b82f6',
            'stripe_customer_id'     => $session->customer,
            'stripe_subscription_id' => $session->subscription,
            'subscription_status'    => $trialDays > 0 ? 'trial' : 'active',
            'trial_ends_at'          => $trialDays > 0 ? now()->addDays($trialDays) : null,
        ]);

        // Bind the new club so BelongsToClub scopes work
        app()->instance('currentClub', $club);

        // Create or update the admin user
        $user = User::firstOrCreate(
            ['email' => $adminEmail],
            [
                'name'              => $adminName,
                'role'              => 'admin',
                'password'          => Hash::make(Str::random(32)),
                'club_id'           => $club->id,
                'email_verified_at' => now(),
            ]
        );

        if ($user->wasRecentlyCreated) {
            $user->club_id = $club->id;
            $user->role    = 'admin';
            $user->save();
        }

        // Send welcome email with password setup link
        $token = Password::createToken($user);
        $user->notify(new NewClubWelcome($token, $club->name));

        Log::info("New club provisioned: {$club->name} (slug: {$slug}) for {$adminEmail}");
    }

    private function handleSubscriptionUpdated(object $subscription): void
    {
        $club = Club::where('stripe_subscription_id', $subscription->id)->first()
              ?? Club::where('stripe_customer_id', $subscription->customer)->first();

        if (! $club) {
            return;
        }

        $status = match ($subscription->status) {
            'active'   => 'active',
            'trialing' => 'trial',
            'past_due' => 'past_due',
            default    => 'inactive',
        };

        $club->update([
            'subscription_status'    => $status,
            'stripe_subscription_id' => $subscription->id,
            'stripe_price_id'        => $subscription->items->data[0]->price->id ?? null,
        ]);

        Log::info("Club {$club->name} subscription updated to: {$status}");
    }

    private function handleSubscriptionDeleted(object $subscription): void
    {
        $club = Club::where('stripe_subscription_id', $subscription->id)->first()
              ?? Club::where('stripe_customer_id', $subscription->customer)->first();

        if (! $club) {
            return;
        }

        $club->update(['subscription_status' => 'cancelled']);

        Log::info("Club {$club->name} subscription cancelled");
    }
}
