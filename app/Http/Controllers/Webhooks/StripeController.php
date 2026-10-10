<?php

namespace App\Http\Controllers\Webhooks;

use App\Http\Controllers\Controller;
use App\Models\Club;
use App\Models\User;
use Illuminate\Http\Request;
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

        try {
            $event = Webhook::constructEvent($payload, $sig, $secret);
        } catch (SignatureVerificationException $e) {
            Log::warning('Stripe webhook signature mismatch: ' . $e->getMessage());
            return response('Invalid signature', 400);
        } catch (\Exception $e) {
            Log::error('Stripe webhook error: ' . $e->getMessage());
            return response('Webhook error', 400);
        }

        match ($event->type) {
            'checkout.session.completed'    => $this->handleCheckoutCompleted($event->data->object),
            'customer.subscription.updated' => $this->handleSubscriptionUpdated($event->data->object),
            'customer.subscription.deleted' => $this->handleSubscriptionDeleted($event->data->object),
            default                         => null,
        };

        return response('OK', 200);
    }

    private function handleCheckoutCompleted(object $session): void
    {
        $meta = (array) $session->metadata;

        $clubName   = $meta['club_name']   ?? 'New Club';
        $adminName  = $meta['admin_name']  ?? 'Admin';
        $adminEmail = $meta['admin_email'] ?? null;

        if (! $adminEmail) {
            Log::error('Stripe checkout.session.completed: missing admin_email in metadata', ['session' => $session->id]);
            return;
        }

        // Generate a URL-safe slug from the club name
        $baseSlug = Str::slug($clubName);
        $slug     = $baseSlug;
        $i        = 2;
        while (Club::where('slug', $slug)->exists()) {
            $slug = $baseSlug . '-' . $i++;
        }

        $club = Club::create([
            'name'                   => $clubName,
            'slug'                   => $slug,
            'primary_color'          => '#111827',
            'accent_color'           => '#3b82f6',
            'stripe_customer_id'     => $session->customer,
            'stripe_subscription_id' => $session->subscription,
            'subscription_status'    => 'trial',
            'trial_ends_at'          => now()->addDays(config('services.stripe.trial_days', 14)),
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

        // Send a password-reset link so they can set their own password
        Password::sendResetLink(['email' => $adminEmail]);

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
