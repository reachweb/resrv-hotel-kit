# Demo data guide

Everything below is created by `database/seeders/ResrvDemoSeeder.php` (idempotent —
re-run any time to refresh the rolling 12-month availability window; past dates are
pruned). Each piece exists to demonstrate a specific Resrv capability.

The installer runs the seeder when you answer yes to the demo data. If you said no,
Resrv starts empty and you can run it later:

```bash
php artisan db:seed --class="Database\Seeders\ResrvDemoSeeder"
```

If you left out the spa or the restaurant, the seeder skips it: no treatment or
sitting rates, and without the spa no In-room Spa Treatment extra or demo spa
appointment.

## Rooms & rate plans

| Room | Base €/night | Allotment | Notes |
| --- | --- | --- | --- |
| Garden View Double | 180 | 4 | The entry-level room |
| Sea View Suite | 320 | 3 | Carries every targeted rate — best rate-comparison demo |
| Terracotta Villa | 640 | 1 | Single-unit scarcity (sells out first) |
| Coastal Family Room | 290 | 3 | Has the connecting-room option |

Room details such as beds, terrace and private pool are terms in the one **Amenities**
taxonomy (there is no separate room-features taxonomy).

Weekend nights (Fri/Sat) carry a +15% uplift; two blackout nights ~3 weeks out
demonstrate sold-out handling.

| Rate plan | Demonstrates |
| --- | --- |
| **Best Flexible** | Independent pricing + inventory, free cancellation (7 days), applies to all rooms — owns the single inventory pool every other rate shares |
| **Advance Saver** (−18%) | Relative **percentage** pricing, **shared availability** (child of Best Flexible), **non-refundable** |
| **Bed & Breakfast** (+€25/night) | Relative **fixed** per-night pricing, shared availability, free cancellation (3 days), **targeted to a subset** of rooms (not apply-to-all) |

No rate carries stay-length or date restrictions, so every rate a room offers is
bookable on any search outside the blackout nights.

## Spa & restaurant (single-date products)

- **6 treatments** with collection-scoped **duration-tier rates** (45/60/90 min) —
  per-treatment prices live in the availability rows (massage €90/€130, couples
  ritual €175, …). Capacity = appointments per day; the spa is **closed Tuesdays**
  (no availability rows — the engine refuses the day regardless of UI).
- **La Marea** restaurant: **Lunch (€35/table, 12 tables)** and **Dinner (€55/table,
  16 tables)** sittings as independent rate pools — priced **per table**, so headcount
  never scales the price (the reservation books one table). **Closed Mondays** (no
  availability rows either).
- The engine only knows a closed day as one with no availability. The front end names
  it from two content fields: **Closed days** on the `treatments_grid` set (/spa,
  Tuesday) and on the La Marea entry (Monday). They disable those weekdays in the
  calendars, add "Closed Mondays" to the hours line and the day list, and turn a
  closed day into "Closed on …" instead of "No availability". Keep each field in step
  with the availability you seed: change one and change the other.
- Free required **Options** carry the time-of-day choice and headcount (appointment
  time per treatment; seating time + party size on the venue — sitting-agnostic, since
  options are not rate-scoped) — Resrv has no time-of-day primitive, options are the pattern.

## Extras

Grouped at checkout into two **categories**: *Your stay* (transfer, hamper,
champagne, late check-out) and *Experiences* (spa treatment, boat tour).

| Extra | Demonstrates |
| --- | --- |
| Airport Transfer (€55) | Fixed price, single |
| Breakfast Hamper (€18) | **Per-day** pricing |
| Champagne on Arrival (€45) | Multiples (up to 5) + **cross-product** — also attached to La Marea (a table booking takes it at its fixed price) |
| In-room Spa Treatment (€90) | Multiples (up to 4) |
| Private Boat Tour (€220) | High-value experience extra |
| Late Check-out (€40) | **Conditional** — only shown for stays of 2+ nights |

## Options (per room, stored in DB)

Breakfast (none/continental/full — **per-day** pricing), bed setup (king/twin —
free, required) and the family room's connecting-room option (fixed). The airport
transfer is an **extra**, not an option.

## Pricing campaigns & coupon

| Campaign | Demonstrates |
| --- | --- |
| Stay 7+ nights — Save 10% | Condition on **reservation duration** |
| Early Bird — 30+ days ahead, Save 12% | Condition on **days to reservation** |
| `COASTAL10` — 10% off, expires +6 months | **Coupon** (dynamic pricing with a code), the checkout "Have a code?" flow |

The three **Offers** entries are the marketing pages for these: *Stay 7, Save 10%*,
*Early Bird · Save 12%* and *Coastal Escape · 10% off* (code `COASTAL10`). They carry
no dates of their own. The discount, its conditions and the coupon's expiry all live
in Resrv, so an offer page can't drift out of date.

## Affiliate

`MEDTRAVEL` (Mediterranean Travel Co, 8% fee, 30-day cookie) — visit any page with
`?afid=MEDTRAVEL` to set the attribution cookie, then book; the reservation shows the
affiliate in the CP and reports.

## Optional demo reservations

Set `RESRV_SEED_DEMO_RESERVATIONS=true` and re-seed to add four reservations
(references `DEMO01`–`DEMO04`): a past confirmed stay and an upcoming one, a confirmed
spa appointment (multi-product reports; only when the spa is installed), and one **expired** checkout with customer
data left unsent — run `php artisan resrv:send-abandoned-emails` to watch the
abandoned-recovery flow pick it up. Off by default because re-seeding resets the
availability counts their holds consumed.
