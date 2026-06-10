# Resrv Hotel

A coastal-luxury hotel starter kit for [Statamic 6](https://statamic.dev) showcasing the
[Resrv](https://statamic.com/addons/reach-web/resrv) reservations addon — one Mediterranean
property with **rooms, spa treatments and restaurant sittings all bookable through a single
checkout**, demo data included.

## What's inside

- **Three bookable products** on one Resrv install: nightly room stays (range calendar),
  spa appointments and restaurant table sittings (single-date calendar + party size).
- **Five rooms / five rate plans** exercising every Resrv rate capability — independent,
  relative (−18% saver), targeted B&B, shared allotment, restricted last-minute — plus
  extras (incl. one conditional), per-room options, two dynamic-pricing campaigns,
  a `COASTAL10` coupon and a demo affiliate.
- **Rolling 12-month availability** seeded relative to install day — the demo never goes stale.
- A complete marketing site: page-builder driven pages (12 block types), offers,
  experiences, FAQs, reviews, optional journal — all Antlers + Tailwind v4 + the
  Livewire-bundled Alpine.
- Restyled Resrv checkout, search, calendar and result components (Blade overrides —
  Livewire structure untouched).

## Requirements

- PHP 8.4+ · Composer
- Statamic **6** (Pro recommended) · Laravel 13
- Node 20+ (Tailwind v4 via Vite)
- A [Resrv license](https://statamic.com/addons/reach-web/resrv) for production use —
  the addon is a paid composer dependency declared by this kit and installed from your
  vendor account; this kit does not bundle or relicense it.

## Install

```bash
statamic new my-hotel reachweb/resrv-hotel
```

The installer prompts per module:

| Module | Default | What it installs |
| --- | --- | --- |
| `demo_content` | yes | All demo entries (pages, rooms, spa, restaurant, offers, FAQs, experiences) + the database seeder (rates, extras, options, pricing, coupon, affiliate, 12-month availability) |
| `payments` | offline | `offline` (keyless, instant demo) · `stripe` (single gateway) · `both` (gateway picker at checkout) |
| `reviews` | yes | Demo guest reviews/testimonials entries |
| `journal` | no | Journal/blog collection, taxonomies, templates and a demo article or two |

The post-install hook runs the migrations, seeds the demo data (when chosen) and applies
your payment choice — `stripe`/`both` rewrite `config/resrv-config.php` and add
`RESRV_STRIPE_*` placeholders to `.env` (see `STRIPE.md`).

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
  `/checkout-complete`, `/rooms`, `/rooms/*`, `/spa/*`, the dining page, and the
  Livewire endpoint `/livewire/*` is never cacheable. Half caching (`half` strategy)
  with those exclusions is the safe option; full static caching of those URLs breaks
  search and checkout.
- **Re-seeding:** `php artisan db:seed --class="Database\Seeders\ResrvDemoSeeder"` is
  idempotent and refreshes the rolling availability window (it also prunes past dates).
  Optional demo reservations for the CP reports/abandoned-recovery demo:
  `RESRV_SEED_DEMO_RESERVATIONS=true` before seeding.
- The demo CP user is **not** created by the kit — make one with
  `php please make:user --super`.

## License

The kit's own code is released under the MIT license. **Resrv is a separate, paid
addon** by [Reach Web](https://reach.gr) — it is installed as a composer dependency
and licensed independently via the Statamic Marketplace.
