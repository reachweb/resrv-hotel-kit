# Demo data guide

Everything below is created by `database/seeders/ResrvDemoSeeder.php` (idempotent —
re-run any time to refresh the rolling 12-month availability window; past dates are
pruned). Each piece exists to demonstrate a specific Resrv capability.

## Rooms & rate plans

| Room | Base €/night | Allotment | Notes |
| --- | --- | --- | --- |
| Garden View Double | 180 | 4 | The entry-level room |
| Sea View Suite | 320 | 3 | Carries every targeted rate — best rate-comparison demo |
| Terracotta Villa | 640 | 1 | Single-unit scarcity (sells out first) |
| Coastal Family Room | 290 | 3 | Has the connecting-room option |
| Cliffside Honeymoon Suite | 480 | 2 | Shared-allotment partner rate |

Weekend nights (Fri/Sat) carry a +15% uplift; two blackout nights ~3 weeks out
demonstrate sold-out handling.

| Rate plan | Demonstrates |
| --- | --- |
| **Best Flexible** | Independent pricing + inventory, free cancellation (7 days), applies to all rooms — the base every relative rate references |
| **Advance Saver** (−18%) | Relative pricing off Best Flexible, own allotment, **non-refundable** |
| **Bed & Breakfast** (+€25) | Independent rate **targeted to a subset** of rooms (not apply-to-all) |
| **Partner Allotment** | **Shared availability** drawing from Best Flexible's pool, capped at 2, inherits the global cancellation default |
| **Last-Minute Escape** (−12%) | The full restriction set: 3-month seasonal window, 2–14 night stays, bookable 1–21 days before arrival, non-refundable |

## Spa & restaurant (single-date products)

- **6 treatments** with collection-scoped **duration-tier rates** (45/60/90 min) —
  per-treatment prices live in the availability rows (massage €90/€130, couples
  ritual €175, …). Capacity = appointments per day; the spa is **closed Tuesdays**
  (no availability rows — the engine refuses the day regardless of UI).
- **La Marea** restaurant: **Lunch (€35/guest, 20 covers)** and **Dinner (€55/guest,
  30 covers)** sittings as independent rate pools; party size rides the reservation
  quantity. **Closed Mondays.**
- Free required **Options** carry the time-of-day choice (appointment time / arrival
  time per sitting) — Resrv has no time-of-day primitive, options are the pattern.

## Extras

| Extra | Demonstrates |
| --- | --- |
| Airport Transfer (€55) | Fixed price, single |
| Breakfast Hamper (€18) | **Per-day** pricing |
| Champagne on Arrival (€45) | Multiples (up to 5) + **cross-product** — also attached to La Marea, where fixed extras scale × party size |
| In-room Spa Treatment (€90) | Multiples, category Wellness |
| Private Boat Tour (€220) | High-value experience extra |
| Late Check-out (€40) | **Conditional** — only shown for stays of 2+ nights — and uncategorised |

## Options (per room, stored in DB)

Breakfast (none/continental/full — **per-day** pricing), transfer vehicle
(sedan/van/luxury — fixed), bed setup (king/twin — free, required) and the family
room's connecting-room option (fixed).

## Pricing campaigns & coupon

| Campaign | Demonstrates |
| --- | --- |
| Stay 7+ nights — Save 10% | Condition on **reservation duration** |
| Early Bird — 30+ days ahead, Save 12% | Condition on **days to reservation** |
| `COASTAL10` — 10% off, expires +6 months | **Coupon** (dynamic pricing with a code), the checkout "Have a code?" flow |

## Affiliate

`MEDTRAVEL` (Mediterranean Travel Co, 8% fee, 30-day cookie) — visit any page with
`?afid=MEDTRAVEL` to set the attribution cookie, then book; the reservation shows the
affiliate in the CP and reports.

## Optional demo reservations

Set `RESRV_SEED_DEMO_RESERVATIONS=true` and re-seed to add four reservations
(references `DEMO01`–`DEMO04`): a past confirmed stay and an upcoming one, a confirmed
spa appointment (multi-product reports), and one **expired** checkout with customer
data left unsent — run `php artisan resrv:send-abandoned-emails` to watch the
abandoned-recovery flow pick it up. Off by default because re-seeding resets the
availability counts their holds consumed.
