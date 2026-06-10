# Stripe payments

The Resrv Hotel kit ships with the **offline** gateway active (bank transfer / pay
at property) so a fresh install takes bookings with zero API keys. If you chose the
**Stripe** or **Both** option during install, the post-install hook already updated
`config/resrv-config.php` and added the env placeholders below — you only need to
fill in your keys and register the webhook. If you chose **Offline** and want card
payments later, follow the "Enabling Stripe by hand" section.

## 1. API keys

Add your keys (Stripe Dashboard → Developers → API keys) to `.env`:

```dotenv
RESRV_STRIPE_SECRET=sk_test_...
RESRV_STRIPE_PUBLISHABLE=pk_test_...
RESRV_STRIPE_WEBHOOK_SECRET=whsec_...   # created in step 2
```

The site layout loads `https://js.stripe.com/v3/` only when
`RESRV_STRIPE_PUBLISHABLE` is set — offline-only installs ship no Stripe
references at all.

## 2. Webhook

Resrv confirms reservations from Stripe's webhook (signature-verified), reacting to
`payment_intent.succeeded` and `payment_intent.payment_failed`.

In the Stripe Dashboard → Developers → Webhooks, add an endpoint:

| Install choice | Endpoint URL |
| --- | --- |
| Stripe only (`payment_gateway`) | `https://your-site.com/resrv/api/webhook` |
| Both (`payment_gateways` picker) | `https://your-site.com/resrv/api/webhook/stripe` |

Select at least the `payment_intent.succeeded` and `payment_intent.payment_failed`
events, then copy the signing secret into `RESRV_STRIPE_WEBHOOK_SECRET`.

For local testing, the Stripe CLI forwards events:

```bash
stripe listen --forward-to your-site.test/resrv/api/webhook
```

## 3. Enabling Stripe by hand

In `config/resrv-config.php`, either swap the single gateway:

```php
'payment_gateway' => \Reach\StatamicResrv\Http\Payment\StripePaymentGateway::class,
```

…or populate `payment_gateways` to offer guests a choice at checkout (the first
entry is the default; an optional per-gateway `surcharge` is supported — see the
comment block in the config file):

```php
'payment_gateways' => [
    'stripe' => [
        'class' => \Reach\StatamicResrv\Http\Payment\StripePaymentGateway::class,
        'label' => 'Credit Card',
    ],
    'offline' => [
        'class' => \Reach\StatamicResrv\Http\Payment\OfflinePaymentGateway::class,
        'label' => 'Bank Transfer / Pay at Property',
    ],
],
```

Then clear the config cache (`php artisan config:clear`) and run a test booking
with Stripe's test card `4242 4242 4242 4242`.
