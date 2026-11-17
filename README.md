# Resrv Hotel

A coastal-luxury hotel starter kit for [Statamic 6](https://statamic.dev) showcasing the
[Resrv](https://statamic.com/addons/reach-web/resrv) reservations addon — one Mediterranean
property with **rooms, spa treatments and restaurant sittings all bookable through a single
checkout**, demo data included.

## What's inside

- **Three bookable products** on one Resrv install: nightly room stays (range calendar),
  spa appointments and restaurant table sittings (single-date calendar + party size).
- **Four rooms / three rate plans** spanning Resrv's rate capabilities — a Best Flexible base
  (independent pricing, owns the inventory pool) plus two rates that **share its availability**:
  a non-refundable Advance Saver (−18%) and a Bed & Breakfast (+€25/night) targeted to some
  rooms — plus categorised extras (incl. one conditional), per-room options, two
  dynamic-pricing campaigns, a `COASTAL10` coupon and a demo affiliate.
- **Rolling 12-month availability** seeded relative to install day — the demo never goes stale.
- A complete marketing site: page-builder driven pages (13 block types), offers,
  FAQs, reviews, optional journal — all Antlers + Tailwind v4 + the
  Livewire-bundled Alpine.
- Restyled Resrv checkout, search, calendar and result components (Blade overrides —
  Livewire structure untouched).

## Requirements

- PHP 8.4+ · Composer
- Statamic **6** (Pro recommended) · Laravel 13 · Resrv **6.5.4+**
- Node 20+ (Tailwind v4 via Vite)
- A [Resrv license](https://statamic.com/addons/reach-web/resrv) for production use —
  the addon is a paid composer dependency declared by this kit and installed from your
  vendor account; this kit does not bundle or relicense it.

## Install

```bash
statamic new my-hotel reachweb/resrv-hotel-kit
```

The installer asks a question per module. A non-interactive install
(`--no-interaction`) takes the defaults.

| Module | Default | What it installs |
| --- | --- | --- |
| `demo_content` | yes | The demo pages, rooms, offers and FAQs, plus the database seeder. The three questions below are asked only after a yes |
| ↳ `spa` | yes | The six spa treatments, the `/spa` page and the spa FAQ |
| ↳ `restaurant` | yes | La Marea and the `/dining` page |
| ↳ `resrv_data` | yes | Fills Resrv with the demo data: rates, extras, options, pricing campaigns, coupon, affiliate and 12-month availability. Say no to start with Resrv empty |
| `payments` | offline | `offline` (keyless, instant demo) · `stripe` (single gateway) · `both` (gateway picker at checkout) |
| `reviews` | yes | Demo guest reviews/testimonials entries |
| `journal` | no | Journal/blog collection, taxonomies, templates and a demo article or two (add `/journal` to a nav yourself) |

The post-install hook runs the migrations, seeds the demo data (when chosen) and applies
your payment choice — `stripe`/`both` rewrite `config/resrv-config.php` and add
`RESRV_STRIPE_*` placeholders to `.env` (see `STRIPE.md`).

Leaving out the spa or the restaurant: their collections, blueprints and templates
still install, empty, so you can add your own later. The hook removes the demo
content's links to the page you left out (the home tile, the About page's La Marea
button, the nav items), and the seeder skips that venue. Without the demo data, the
hook registers the demo entries with Resrv instead, so you can add availability and
rates to them in the CP; the seeder still ships if you want the demo data later.

After install:

```bash
npm install && npm run build    # compile Tailwind v4 + site JS
php artisan serve               # or your usual valet/herd setup
```

Then add photography per `IMAGES.md` — every image in the kit is a **named slot** that
renders a graceful labelled placeholder until you drop the real file in.

## After-install docs

| File | What it covers |
| --- | --- |
| `IMAGES.md` | The full image-slot manifest: folder, filename, aspect ratio, where it appears |
| `DEMO-DATA.md` | What each demo room/rate/extra/campaign/coupon/affiliate demonstrates |
| `CALENDAR-THEMING.md` | Retheming the booking calendar via CSS variables |
| `STRIPE.md` | Enabling Stripe: keys, webhooks, the gateway picker |

## Things to know

- **`config/cache.php` (`serializable_classes: true`) and `config/session.php`
  (`serialization: 'php'`) are intentional** and ship with the kit. Resrv persists
  real objects in the cache and session; Laravel's defaults silently break dynamic
  pricing and 500 the second search request. Don't revert them.
- **Static caching:** if you enable Statamic's static caching, you must exclude the
  booking surfaces — they're session-driven Livewire. At minimum exclude `/checkout`,
  `/checkout-complete`, `/manage-booking`, `/pay`, `/rooms`, `/rooms/*`, `/spa/*`, the
  dining page, and the Livewire endpoint `/livewire/*` is never cacheable. Half caching (`half` strategy)
  with those exclusions is the safe option; full static caching of those URLs breaks
  search and checkout.
- **Re-seeding:** `php artisan db:seed --class="Database\Seeders\ResrvDemoSeeder"` is
  idempotent and refreshes the rolling availability window (it also prunes past dates).
  It also fills an install made without the demo data.
  Optional demo reservations for the CP reports/abandoned-recovery demo:
  `RESRV_SEED_DEMO_RESERVATIONS=true` before seeding.
- **Guest self-service pages:** `/manage-booking` (Resrv's reservation status page)
  lets guests look up a booking by email and reference, or open it from the "Manage
  your booking" link in the confirmation email, and cancel it inside the free
  cancellation window. Offline bookings are view only. `/pay` is the payment page for
  reservations you create in the CP: guests reach it from the signed link in the
  payment request email. Both are wired under **Resrv → Settings → Checkout**. Replace
  the demo **Offline Payment Instructions** (bank details) there before going live.
- **Scheduler:** `routes/console.php` schedules `resrv:cancel-lapsed-holds` (every 15
  minutes) and `resrv:housekeeping` (daily). In production, add the standard Laravel cron
  entry (`* * * * * php artisan schedule:run`). Without it, unpaid reservations created
  from the CP stay **Awaiting payment** past their deadline and keep holding availability.
- **Logo:** the "resrv." wordmark is inline SVG in `resources/views/partials/logo.antlers.html`
  (it inherits the header's colour switch via `currentColor`). Changing the `site` global's
  brand name updates titles and the logo's accessible label, not the artwork: to rebrand,
  replace that partial plus `public/favicon.ico`, `public/favicon.svg` and
  `public/apple-touch-icon.png`.
- The demo CP user is **not** created by the kit — make one with
  `php please make:user --super`.

## License

The kit's own code is released under the MIT license. **Resrv is a separate, paid
addon** by [Reach Web](https://reach.gr) — it is installed as a composer dependency
and licensed independently via the Statamic Marketplace.
