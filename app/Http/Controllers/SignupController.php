<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Stripe\Stripe;
use Stripe\Checkout\Session as StripeSession;

class SignupController extends Controller
{
    public function pricing()
    {
        return view('signup.pricing');
    }

    public function checkout(Request $request)
    {
        $request->validate([
            'club_name'  => 'required|string|max:100',
            'admin_name' => 'required|string|max:100',
            'admin_email'=> 'required|email|max:255',
        ]);

        Stripe::setApiKey(config('services.stripe.secret'));

        $session = StripeSession::create([
            'mode'                 => 'subscription',
            'payment_method_types' => ['card'],
            'line_items'           => [[
                'price'    => config('services.stripe.price_id'),
                'quantity' => 1,
            ]],
            'subscription_data' => [
                'trial_period_days' => config('services.stripe.trial_days', 14),
            ],
            'customer_email' => $request->admin_email,
            'metadata' => [
                'club_name'   => $request->club_name,
                'admin_name'  => $request->admin_name,
                'admin_email' => $request->admin_email,
            ],
            'success_url' => route('signup.success') . '?session_id={CHECKOUT_SESSION_ID}',
            'cancel_url'  => route('signup.pricing'),
        ]);

        return redirect($session->url);
    }

    public function success(Request $request)
    {
        return view('signup.success');
    }
}
