<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Reach\StatamicResrv\Models\Affiliate;
use Reach\StatamicResrv\Models\Availability;
use Reach\StatamicResrv\Models\Customer;
use Reach\StatamicResrv\Models\DynamicPricing;
use Reach\StatamicResrv\Models\Entry as ResrvEntry;
use Reach\StatamicResrv\Models\Extra;
use Reach\StatamicResrv\Models\ExtraCategory;
use Reach\StatamicResrv\Models\Option;
use Reach\StatamicResrv\Models\OptionValue;
use Reach\StatamicResrv\Models\Rate;
use Reach\StatamicResrv\Models\Reservation;
use Statamic\Facades\Entry;
use Statamic\Support\Str;

/**
 * Resrv Hotel — demo data seeder.
 *
 * Hand-builds the full feature-exercising Resrv dataset (rates, extras, options,
 * dynamic pricing, coupon, affiliate, rolling availability) directly against the
 * Resrv Eloquent models — the same models the Control Panel writes through. Every
 * write is idempotent (updateOrCreate / updateOrInsert on a natural key) so the
 * seeder can run repeatedly without creating duplicates.
 *
 * Ordering is strict and load-bearing:
 *   entries (flat content must already exist) -> resrv_entries mapping
 *   -> rates -> availability (FK: resrv_availabilities.rate_id -> resrv_rates.id)
 *   -> extras/categories/conditions -> options/values -> dynamic pricing + coupon
 *   -> affiliate -> single-date bookables (spa treatments + restaurant, Appendix J),
 *   which reuse the same helpers parameterised by collection.
 *
 * Availability is generated relative to today() so installs always have future
 * dates — a rolling 12-month window, with past rows pruned so re-running the seeder
 * on a long-lived demo keeps the table tidy. Optionally (RESRV_SEED_DEMO_RESERVATIONS=true)
 * seeds a handful of demo reservations, including one expired-unsent for the
 * abandoned-recovery (resrv:send-abandoned-emails) + reports demo.
 */
class ResrvDemoSeeder extends Seeder
{
    /** How many months of rolling availability to generate from today. */
    private const MONTHS_AHEAD = 12;

    /** The morph type stored in resrv_dynamic_pricing_assignments for entry (availability) targets. */
    private const AVAILABILITY_MORPH = Availability::class;

    /**
     * Rooms keyed by slug. `base` = nightly reference price (the figure stored in
     * availability rows; relative rates derive their displayed price from it).
     * `allotment` = rooms available for the main (Best Flexible) inventory.
     */
    private array $rooms = [
        'garden-view-double' => ['base' => 180, 'allotment' => 4],
        'sea-view-suite' => ['base' => 320, 'allotment' => 3],
        'terracotta-villa' => ['base' => 640, 'allotment' => 1],
        'coastal-family-room' => ['base' => 290, 'allotment' => 3],
    ];

    /** The weekday the spa is closed — no availability rows are generated for it. */
    private const SPA_CLOSED_WEEKDAY = Carbon::TUESDAY;

    /** The weekday La Marea is closed — no availability rows are generated for it. */
    private const RESTAURANT_CLOSED_WEEKDAY = Carbon::MONDAY;

    /**
     * Spa treatments keyed by slug (Appendix J). `tiers` = duration-rate slug =>
     * appointment price for that treatment; `slots` = bookable appointments per open
     * day (the search quantity counts appointments — the couples ritual is a single
     * appointment for two); `times` = the free appointment-time option values.
     */
    private array $treatments = [
        'mediterranean-massage' => ['tiers' => ['60-min' => 90, '90-min' => 130], 'slots' => 4, 'times' => ['10:00', '12:00', '16:00', '18:00']],
        'sea-salt-body-polish' => ['tiers' => ['45-min' => 70], 'slots' => 3, 'times' => ['10:00', '12:00', '16:00', '18:00']],
        'garden-botanicals-facial' => ['tiers' => ['60-min' => 85], 'slots' => 3, 'times' => ['10:00', '12:00', '16:00', '18:00']],
        'couples-ritual' => ['tiers' => ['90-min' => 175], 'slots' => 1, 'times' => ['16:00', '18:00']],
        'morning-stretch-steam' => ['tiers' => ['45-min' => 40], 'slots' => 8, 'times' => ['07:30', '08:30']],
        'in-room-treatment' => ['tiers' => ['60-min' => 90], 'slots' => 2, 'times' => ['10:00', '12:00', '16:00', '18:00']],
    ];

    /** Duration-tier rate titles for the spa_treatments collection, keyed by rate slug. */
    private array $spaTiers = [
        '45-min' => '45 minutes',
        '60-min' => '60 minutes',
        '90-min' => '90 minutes',
    ];

    /**
     * La Marea sittings (collection-scoped rates) — each an independent table pool.
     * `price` is the flat price per table; the reservation books one table (quantity
     * stays 1), so it does not scale with party size. `tables` = tables bookable per
     * open day. Party size and seating time are captured separately as free Options
     * (see seedRestaurant).
     */
    private array $sittings = [
        'lunch-sitting' => [
            'title' => 'Lunch Sitting',
            'description' => 'A long Mediterranean lunch by the water — priced per table, 13:00 to 16:00.',
            'price' => 35,
            'tables' => 12,
        ],
        'dinner-sitting' => [
            'title' => 'Dinner Sitting',
            'description' => 'Dinner as the light softens — priced per table, 19:30 to 22:30.',
            'price' => 55,
            'tables' => 16,
        ],
    ];

    /** Resolved Resrv Rate models, keyed by slug, populated by seedRates(). */
    private array $rates = [];

    /** Resolved Statamic entry ids, keyed by room slug, populated by ensureEntryMappings(). */
    private array $roomIds = [];

    public function run(): void
    {
        $this->pruneStaleAvailability();
        $this->ensureEntryMappings();
        $this->seedRates();
        $this->seedAvailability();
        $this->seedExtras();
        $this->seedOptions();
        $this->seedDynamicPricing();
        $this->seedAffiliate();

        // Appendix J — single-date bookables sharing the same checkout as rooms.
        $this->seedSpaTreatments();
        $this->seedRestaurant();

        // Optional demo reservations (reports + abandoned-recovery showcase). Off by
        // default — they add CP noise and re-seeding resets the availability counts
        // their holds decremented.
        if (env('RESRV_SEED_DEMO_RESERVATIONS', false)) {
            $this->seedDemoReservations();
        }

        $this->command?->info('Resrv Hotel demo data seeded.');
    }

    /**
     * Refresh mode for long-lived demos: drop availability rows behind today so
     * repeated runs keep a clean rolling window instead of accumulating history.
     */
    private function pruneStaleAvailability(): void
    {
        Availability::where('date', '<', Carbon::today()->toDateString())->delete();
    }

    private function ensureEntryMappings(): void
    {
        $this->roomIds = $this->ensureMappingsFor('rooms', array_keys($this->rooms));
    }

    /**
     * Resolve a collection's entries by slug and guarantee their resrv_entries mirror
     * rows exist. Flat-file content created outside Statamic never fired EntrySaved,
     * so the mapping the booking engine resolves availability/rates through is
     * missing — create it.
     *
     * The mapping is written directly rather than via Entry::syncToDatabase() on purpose:
     * a seeder must not depend on the EntrySaved listener firing, and a direct write is
     * deterministic and side-effect free regardless of how the content was created.
     *
     * @return array<string, string> entry ids keyed by slug
     */
    private function ensureMappingsFor(string $collection, array $slugs): array
    {
        $ids = [];

        foreach ($slugs as $slug) {
            $entry = Entry::query()->where('collection', $collection)->where('slug', $slug)->first();

            if (! $entry) {
                throw new \RuntimeException(
                    "ResrvDemoSeeder: {$collection} entry '{$slug}' not found. Install the demo content before seeding."
                );
            }

            $ids[$slug] = $entry->id();

            ResrvEntry::updateOrCreate(
                ['item_id' => $entry->id()],
                [
                    'title' => $entry->get('title'),
                    'enabled' => true,
                    'collection' => $collection,
                    'handle' => $entry->blueprint()->handle(),
                ]
            );
        }

        return $ids;
    }

    /**
     * The rate plans for the `rooms` collection. One global base rate plus four
     * variations that, between them, exercise every Resrv rate capability:
     * independent + relative pricing, independent + shared availability, all three
     * cancellation states (explicit free-cancellation, non-refundable, inherited),
     * and the full restriction set (date window, min/max stay, min/max days before).
     */
    private function seedRates(): void
    {
        // 1) Best Flexible — independent pricing, independent availability, free cancellation 7d.
        //    The base inventory every other rate references.
        $this->rates['best-flexible'] = Rate::updateOrCreate(
            ['collection' => 'rooms', 'slug' => 'best-flexible'],
            [
                'title' => 'Best Flexible',
                'description' => 'Our most flexible rate — free cancellation up to 7 days before arrival.',
                'apply_to_all' => true,
                'pricing_type' => 'independent',
                'availability_type' => 'independent',
                'base_rate_id' => null,
                'refundable' => true,
                'cancellation_policy' => 'free_cancellation',
                'free_cancellation_period' => 7,
                'order' => 1,
                'published' => true,
            ]
        );

        $baseId = $this->rates['best-flexible']->id;

        // 2) Advance Saver — relative (-18%) pricing, independent allotment, non-refundable.
        $this->rates['advance-saver'] = Rate::updateOrCreate(
            ['collection' => 'rooms', 'slug' => 'advance-saver'],
            [
                'title' => 'Advance Saver',
                'description' => 'Book early and save 18% — non-refundable.',
                'apply_to_all' => true,
                'pricing_type' => 'relative',
                'base_rate_id' => $baseId,
                'modifier_type' => 'percent',
                'modifier_operation' => 'decrease',
                'modifier_amount' => 18,
                'availability_type' => 'independent',
                'refundable' => false,
                'cancellation_policy' => 'non_refundable',
                'free_cancellation_period' => null,
                'order' => 2,
                'published' => true,
            ]
        );

        // 3) Bed & Breakfast — independent pricing + its own allotment, free cancellation 3d.
        //    Targeted to a subset of rooms (apply_to_all = false + entry pivot).
        $this->rates['bed-breakfast'] = Rate::updateOrCreate(
            ['collection' => 'rooms', 'slug' => 'bed-breakfast'],
            [
                'title' => 'Bed & Breakfast',
                'description' => 'Includes a full Mediterranean breakfast — free cancellation up to 3 days before arrival.',
                'apply_to_all' => false,
                'pricing_type' => 'independent',
                'availability_type' => 'independent',
                'base_rate_id' => null,
                'refundable' => true,
                'cancellation_policy' => 'free_cancellation',
                'free_cancellation_period' => 3,
                'order' => 3,
                'published' => true,
            ]
        );

        // 4) Shared Allotment — independent pricing, SHARED availability (draws from Best
        //    Flexible's pool), capped at 2, cancellation inherits the global default.
        $this->rates['shared-allotment'] = Rate::updateOrCreate(
            ['collection' => 'rooms', 'slug' => 'shared-allotment'],
            [
                'title' => 'Partner Allotment',
                'description' => 'A small shared allotment — limited availability.',
                'apply_to_all' => false,
                'pricing_type' => 'independent',
                'availability_type' => 'shared',
                'base_rate_id' => $baseId,
                'max_available' => 2,
                'require_price_override' => false,
                'refundable' => true,
                'cancellation_policy' => null, // inherit global free-cancellation default
                'free_cancellation_period' => null,
                'order' => 4,
                'published' => true,
            ]
        );

        // 5) Last-Minute / Seasonal — relative (-12%), restricted: seasonal date window,
        //    2–14 night stays, booked 1–21 days before arrival. Non-refundable.
        $this->rates['last-minute-seasonal'] = Rate::updateOrCreate(
            ['collection' => 'rooms', 'slug' => 'last-minute-seasonal'],
            [
                'title' => 'Last-Minute Escape',
                'description' => 'A limited seasonal rate for near-term stays — non-refundable.',
                'apply_to_all' => false,
                'pricing_type' => 'relative',
                'base_rate_id' => $baseId,
                'modifier_type' => 'percent',
                'modifier_operation' => 'decrease',
                'modifier_amount' => 12,
                'availability_type' => 'independent',
                'date_start' => Carbon::today()->startOfDay(),
                'date_end' => Carbon::today()->addMonths(3)->endOfDay(),
                'min_stay' => 2,
                'max_stay' => 14,
                'min_days_before' => 1,
                'max_days_before' => 21,
                'refundable' => false,
                'cancellation_policy' => 'non_refundable',
                'free_cancellation_period' => null,
                'order' => 5,
                'published' => true,
            ]
        );

        // Targeted rate -> room assignments (apply_to_all = false rates only).
        $this->assignRateToRooms('bed-breakfast', ['garden-view-double', 'sea-view-suite', 'coastal-family-room']);
        $this->assignRateToRooms('shared-allotment', ['sea-view-suite']);
        $this->assignRateToRooms('last-minute-seasonal', ['garden-view-double', 'sea-view-suite', 'terracotta-villa']);
    }

    private function assignRateToRooms(string $rateSlug, array $roomSlugs): void
    {
        $rateId = $this->rates[$rateSlug]->id;

        foreach ($roomSlugs as $slug) {
            DB::table('resrv_rate_entries')->updateOrInsert(
                ['rate_id' => $rateId, 'statamic_id' => $this->roomIds[$slug]],
                ['created_at' => now(), 'updated_at' => now()],
            );
        }
    }

    /**
     * Rolling availability. Only rates with their own inventory get rows: every
     * independent rate (including relative-independent rates, which store the
     * reference price and have the modifier applied at query time). Shared rates
     * read their base rate's rows, so they are intentionally skipped here.
     */
    private function seedAvailability(): void
    {
        $start = Carbon::today()->startOfDay();
        $end = $start->copy()->addMonths(self::MONTHS_AHEAD);

        foreach ($this->rooms as $slug => $cfg) {
            $entryId = $this->roomIds[$slug];

            // (rate slug => [reference price, allotment, optional date-window clamp])
            $plans = [
                'best-flexible' => ['price' => $cfg['base'], 'available' => $cfg['allotment']],
                'advance-saver' => ['price' => $cfg['base'], 'available' => min(2, $cfg['allotment'])],
            ];

            if ($this->roomHasRate($slug, 'bed-breakfast')) {
                // B&B carries a breakfast premium baked into its own reference price.
                $plans['bed-breakfast'] = ['price' => $cfg['base'] + 25, 'available' => min(2, $cfg['allotment'])];
            }

            if ($this->roomHasRate($slug, 'last-minute-seasonal')) {
                // Only seed within the seasonal window the rate itself allows.
                $plans['last-minute-seasonal'] = [
                    'price' => $cfg['base'],
                    'available' => min(2, $cfg['allotment']),
                    'until' => $start->copy()->addMonths(3),
                ];
            }

            DB::transaction(function () use ($entryId, $slug, $plans, $start, $end) {
                foreach ($plans as $rateSlug => $plan) {
                    $rateId = $this->rates[$rateSlug]->id;
                    $until = $plan['until'] ?? $end;

                    for ($date = $start->copy(); $date->lte($until); $date->addDay()) {
                        Availability::updateOrCreate(
                            ['statamic_id' => $entryId, 'date' => $date->toDateString(), 'rate_id' => $rateId],
                            [
                                'available' => $this->availableFor($slug, $rateSlug, $date, $plan['available']),
                                'price' => $this->priceFor($date, $plan['price']),
                            ],
                        );
                    }
                }
            });
        }
    }

    /** Weekend (Fri/Sat night) uplift so prices aren't flat across the calendar. */
    private function priceFor(Carbon $date, int $reference): float
    {
        $isWeekend = in_array($date->dayOfWeek, [Carbon::FRIDAY, Carbon::SATURDAY], true);

        return $isWeekend ? round($reference * 1.15) : $reference;
    }

    /**
     * A couple of blackout nights per room on the base rate (~3 weeks out) to
     * demonstrate sold-out handling; every other night uses the plan allotment.
     */
    private function availableFor(string $slug, string $rateSlug, Carbon $date, int $allotment): int
    {
        if ($rateSlug === 'best-flexible') {
            $blackout = [
                Carbon::today()->addDays(21)->toDateString(),
                Carbon::today()->addDays(22)->toDateString(),
            ];
            if (in_array($date->toDateString(), $blackout, true)) {
                return 0;
            }
        }

        return $allotment;
    }

    private function roomHasRate(string $slug, string $rateSlug): bool
    {
        return DB::table('resrv_rate_entries')
            ->where('rate_id', $this->rates[$rateSlug]->id)
            ->where('statamic_id', $this->roomIds[$slug])
            ->exists();
    }

    /**
     * Extra categories + extras, including one conditional extra (Late Check-out,
     * shown only for stays of 2+ nights) and an uncategorised extra. Every extra is
     * mass-assigned to every room via the resrv_entry_extra pivot.
     */
    private function seedExtras(): void
    {
        $categories = [];
        foreach (['Transfers', 'Dining', 'Wellness', 'Experiences'] as $i => $name) {
            $categories[$name] = ExtraCategory::updateOrCreate(
                ['slug' => Str::slug($name)],
                ['name' => $name, 'order' => $i + 1, 'published' => true],
            );
        }

        // [slug => attributes]. category null = uncategorised (still a valid extra).
        $extras = [
            'airport-transfer' => [
                'name' => 'Airport Transfer', 'price' => 55, 'price_type' => 'fixed',
                'category' => 'Transfers', 'allow_multiple' => false, 'maximum' => null,
                'description' => 'Private transfer between the airport and the resort.',
            ],
            'breakfast-hamper' => [
                'name' => 'Breakfast Hamper', 'price' => 18, 'price_type' => 'perday',
                'category' => 'Dining', 'allow_multiple' => false, 'maximum' => null,
                'description' => 'A daily basket of local pastries, fruit and coffee delivered to your door.',
            ],
            'champagne-on-arrival' => [
                'name' => 'Champagne on Arrival', 'price' => 45, 'price_type' => 'fixed',
                'category' => 'Dining', 'allow_multiple' => true, 'maximum' => 5,
                'description' => 'A chilled bottle of champagne waiting in your room.',
            ],
            'in-room-spa-treatment' => [
                'name' => 'In-room Spa Treatment', 'price' => 90, 'price_type' => 'fixed',
                'category' => 'Wellness', 'allow_multiple' => true, 'maximum' => 4,
                'description' => 'A 60-minute massage or facial in the comfort of your room.',
            ],
            'private-boat-tour' => [
                'name' => 'Private Boat Tour', 'price' => 220, 'price_type' => 'fixed',
                'category' => 'Experiences', 'allow_multiple' => true, 'maximum' => 3,
                'description' => 'A half-day private boat tour of the nearby coves.',
            ],
            // Conditional + uncategorised: only offered for stays of 2+ nights.
            'late-check-out' => [
                'name' => 'Late Check-out (until 16:00)', 'price' => 40, 'price_type' => 'fixed',
                'category' => null, 'allow_multiple' => false, 'maximum' => null,
                'description' => 'Keep your room until 16:00 on your day of departure.',
            ],
        ];

        $extraModels = [];
        $order = 1;
        foreach ($extras as $slug => $attr) {
            $extraModels[$slug] = Extra::updateOrCreate(
                ['slug' => $slug],
                [
                    'name' => $attr['name'],
                    'price' => $attr['price'],
                    'price_type' => $attr['price_type'],
                    'category_id' => $attr['category'] ? $categories[$attr['category']]->id : null,
                    'allow_multiple' => $attr['allow_multiple'],
                    'maximum' => $attr['maximum'],
                    'description' => $attr['description'],
                    'order' => $order++,
                    'published' => true,
                ],
            );
        }

        // Conditional rule for Late Check-out: show only when reservation duration >= 2 nights.
        DB::table('resrv_extra_conditions')->updateOrInsert(
            ['extra_id' => $extraModels['late-check-out']->id],
            [
                'conditions' => json_encode([[
                    'operation' => 'show',
                    'type' => 'reservation_duration',
                    'comparison' => '>=',
                    'value' => 2,
                ]]),
                'created_at' => now(),
                'updated_at' => now(),
            ],
        );

        // Mass-assign every extra to every room (resrv_entry_extra: entry_id is the
        // resrv_entries row id, not the statamic id).
        foreach ($this->roomIds as $statamicId) {
            $resrvEntryId = ResrvEntry::where('item_id', $statamicId)->value('id');
            foreach ($extraModels as $extra) {
                DB::table('resrv_entry_extra')->updateOrInsert(
                    ['entry_id' => $resrvEntryId, 'extra_id' => $extra->id],
                    ['created_at' => now(), 'updated_at' => now()],
                );
            }
        }
    }

    /**
     * Per-room options (stored in resrv_options / resrv_options_values keyed by the
     * Statamic entry id). Demonstrates free, fixed and per-day option value pricing,
     * plus a connecting-room option on the family room.
     */
    private function seedOptions(): void
    {
        foreach ($this->roomIds as $slug => $statamicId) {
            // Breakfast — per-day pricing.
            $this->seedOption($statamicId, 'breakfast', 'Breakfast', false, [
                ['name' => 'None', 'price' => 0, 'price_type' => 'free'],
                ['name' => 'Continental', 'price' => 12, 'price_type' => 'perday'],
                ['name' => 'Full Mediterranean', 'price' => 20, 'price_type' => 'perday'],
            ]);

            // Bed setup — free choice, only where a twin makes sense.
            if (in_array($slug, ['garden-view-double', 'sea-view-suite', 'coastal-family-room'], true)) {
                $this->seedOption($statamicId, 'bed-setup', 'Bed setup', true, [
                    ['name' => 'King', 'price' => 0, 'price_type' => 'free'],
                    ['name' => 'Twin', 'price' => 0, 'price_type' => 'free'],
                ]);
            }

            // Connecting room — the family room's showcase option.
            if ($slug === 'coastal-family-room') {
                $this->seedOption($statamicId, 'connecting-room', 'Connecting room', false, [
                    ['name' => 'No', 'price' => 0, 'price_type' => 'free'],
                    ['name' => 'Add connecting room', 'price' => 120, 'price_type' => 'fixed'],
                ]);
            }
        }
    }

    private function seedOption(string $statamicId, string $slug, string $name, bool $required, array $values): void
    {
        $option = Option::updateOrCreate(
            ['item_id' => $statamicId, 'slug' => $slug],
            ['name' => $name, 'required' => $required, 'order' => 1, 'published' => true],
        );

        foreach ($values as $i => $value) {
            OptionValue::updateOrCreate(
                ['option_id' => $option->id, 'name' => $value['name']],
                [
                    'price' => $value['price'],
                    'price_type' => $value['price_type'],
                    'order' => $i + 1,
                    'published' => true,
                ],
            );
        }
    }

    /**
     * Two dynamic-pricing campaigns and one coupon. All three are condition/coupon
     * gated rather than date-bound, and are assigned to every room via the morph
     * pivot (resrv_dynamic_pricing_assignments) keyed by the Statamic entry id.
     */
    private function seedDynamicPricing(): void
    {
        // Campaign A — stays of 7+ nights save 10%.
        $weekly = DynamicPricing::updateOrCreate(
            ['title' => 'Stay 7+ nights — Save 10%'],
            [
                'amount_type' => 'percent',
                'amount_operation' => 'decrease',
                'amount' => 10,
                'date_start' => null,
                'date_end' => null,
                'date_include' => null,
                'condition_type' => 'reservation_duration',
                'condition_comparison' => '>=',
                'condition_value' => '7',
                'coupon' => null,
                'expire_at' => null,
                'overrides_all' => false,
                'order' => 1,
                'published' => true,
            ],
        );

        // Campaign B — early bird, booked 30+ days ahead, save 12%.
        $earlyBird = DynamicPricing::updateOrCreate(
            ['title' => 'Early Bird — Book 30+ days ahead, Save 12%'],
            [
                'amount_type' => 'percent',
                'amount_operation' => 'decrease',
                'amount' => 12,
                'date_start' => null,
                'date_end' => null,
                'date_include' => null,
                'condition_type' => 'days_to_reservation',
                'condition_comparison' => '>=',
                'condition_value' => '30',
                'coupon' => null,
                'expire_at' => null,
                'overrides_all' => false,
                'order' => 2,
                'published' => true,
            ],
        );

        // Coupon — COASTAL10, 10% off, expires in 6 months.
        $coupon = DynamicPricing::updateOrCreate(
            ['title' => 'COASTAL10 — 10% off'],
            [
                'amount_type' => 'percent',
                'amount_operation' => 'decrease',
                'amount' => 10,
                'date_start' => null,
                'date_end' => null,
                'date_include' => null,
                'condition_type' => null,
                'condition_comparison' => null,
                'condition_value' => '1',
                'coupon' => 'COASTAL10',
                'expire_at' => Carbon::today()->addMonths(6),
                'overrides_all' => false,
                'order' => 3,
                'published' => true,
            ],
        );

        foreach ([$weekly, $earlyBird, $coupon] as $pricing) {
            foreach ($this->roomIds as $statamicId) {
                DB::table('resrv_dynamic_pricing_assignments')->updateOrInsert(
                    [
                        'dynamic_pricing_id' => $pricing->id,
                        'dynamic_pricing_assignment_id' => $statamicId,
                        'dynamic_pricing_assignment_type' => self::AVAILABILITY_MORPH,
                    ],
                    [],
                );
            }
        }
    }

    /** A single affiliate partner for the attribution / reports demo. */
    private function seedAffiliate(): void
    {
        Affiliate::updateOrCreate(
            ['code' => 'MEDTRAVEL'],
            [
                'name' => 'Mediterranean Travel Co',
                'email' => 'partners@mediterraneantravel.example',
                'cookie_duration' => 30,
                'fee' => 8,
                'published' => true,
                'allow_skipping_payment' => false,
                'send_reservation_email' => false,
            ],
        );
    }

    /**
     * Spa treatments (Appendix J): duration tiers are collection-scoped rates targeted
     * via the rate->entry pivot to the treatments that offer them; the per-treatment
     * appointment price lives in the availability rows. Appointment times are a free
     * required Option per entry (Resrv has no time-of-day primitive).
     */
    private function seedSpaTreatments(): void
    {
        $ids = $this->ensureMappingsFor('spa_treatments', array_keys($this->treatments));

        $tierRates = [];
        $order = 1;
        foreach ($this->spaTiers as $slug => $title) {
            $tierRates[$slug] = Rate::updateOrCreate(
                ['collection' => 'spa_treatments', 'slug' => $slug],
                [
                    'title' => $title,
                    'description' => "A {$title} appointment.",
                    'apply_to_all' => false,
                    'pricing_type' => 'independent',
                    'availability_type' => 'independent',
                    'base_rate_id' => null,
                    'refundable' => true,
                    'cancellation_policy' => 'free_cancellation',
                    'free_cancellation_period' => 1,
                    'order' => $order++,
                    'published' => true,
                ],
            );
        }

        foreach ($this->treatments as $slug => $cfg) {
            foreach (array_keys($cfg['tiers']) as $tier) {
                DB::table('resrv_rate_entries')->updateOrInsert(
                    ['rate_id' => $tierRates[$tier]->id, 'statamic_id' => $ids[$slug]],
                    ['created_at' => now(), 'updated_at' => now()],
                );
            }

            $this->seedOption($ids[$slug], 'appointment-time', 'Appointment time', true, array_map(
                fn (string $time) => ['name' => $time, 'price' => 0, 'price_type' => 'free'],
                $cfg['times'],
            ));

            DB::transaction(function () use ($ids, $slug, $cfg, $tierRates) {
                foreach ($cfg['tiers'] as $tier => $price) {
                    $this->seedSingleDateAvailability(
                        $ids[$slug], $tierRates[$tier]->id, $price, $cfg['slots'], self::SPA_CLOSED_WEEKDAY,
                    );
                }
            });
        }
    }

    /**
     * La Marea (Appendix J): Lunch/Dinner sittings are independent collection-scoped
     * rates, each with its own daily table pool; price is a flat price per table and
     * the reservation books a single table (quantity stays 1), so it does not scale
     * with party size. Arrival time and party size are free required Options. Options
     * are not rate-scoped, so the arrival values are sitting-agnostic (Early/Standard/
     * Late seating) — a concrete clock time would belong to only one sitting and show
     * up wrongly under the other. Champagne is attached to exercise cross-product extras.
     */
    private function seedRestaurant(): void
    {
        $venueId = $this->ensureMappingsFor('restaurant', ['la-marea'])['la-marea'];

        $order = 1;
        $sittingRates = [];
        foreach ($this->sittings as $slug => $cfg) {
            $sittingRates[$slug] = Rate::updateOrCreate(
                ['collection' => 'restaurant', 'slug' => $slug],
                [
                    'title' => $cfg['title'],
                    'description' => $cfg['description'],
                    'apply_to_all' => true,
                    'pricing_type' => 'independent',
                    'availability_type' => 'independent',
                    'base_rate_id' => null,
                    'refundable' => true,
                    'cancellation_policy' => 'free_cancellation',
                    'free_cancellation_period' => 1,
                    'order' => $order++,
                    'published' => true,
                ],
            );
        }

        // Seating time within whichever sitting was booked. Options are not rate-scoped,
        // so these are sitting-agnostic — a concrete clock time (e.g. 13:00) belongs to
        // only one sitting and would mislead under the other.
        $this->seedOption($venueId, 'arrival-time', 'Arrival time', true, [
            ['name' => 'Early seating', 'price' => 0, 'price_type' => 'free'],
            ['name' => 'Standard seating', 'price' => 0, 'price_type' => 'free'],
            ['name' => 'Late seating', 'price' => 0, 'price_type' => 'free'],
        ]);

        // Party size — captured for the kitchen but free: the price is per table, so
        // headcount never scales it (the reservation always books a single table).
        $partySizes = [];
        for ($guests = 1; $guests <= 8; $guests++) {
            $partySizes[] = ['name' => $guests.' '.Str::plural('guest', $guests), 'price' => 0, 'price_type' => 'free'];
        }
        $this->seedOption($venueId, 'party-size', 'Party size', true, $partySizes);

        DB::transaction(function () use ($venueId, $sittingRates) {
            foreach ($this->sittings as $slug => $cfg) {
                $this->seedSingleDateAvailability(
                    $venueId, $sittingRates[$slug]->id, $cfg['price'], $cfg['tables'], self::RESTAURANT_CLOSED_WEEKDAY,
                );
            }
        });

        $resrvEntryId = ResrvEntry::where('item_id', $venueId)->value('id');
        $champagneId = Extra::where('slug', 'champagne-on-arrival')->value('id');
        DB::table('resrv_entry_extra')->updateOrInsert(
            ['entry_id' => $resrvEntryId, 'extra_id' => $champagneId],
            ['created_at' => now(), 'updated_at' => now()],
        );
    }

    /**
     * Optional demo reservations (sub-flag: RESRV_SEED_DEMO_RESERVATIONS=true) for the
     * CP reports + abandoned-recovery showcase. Deliberately minimal: a past and an
     * upcoming confirmed room stay, one confirmed spa appointment, and one EXPIRED
     * reservation with customer data and a NULL abandoned_email_sent_at backdated past
     * the recovery delay — exactly what resrv:send-abandoned-emails targets. Idempotent
     * on the fixed references; timestamps are set explicitly (the model keeps them
     * fillable for this) so re-runs don't drift the backdating. The holds are NOT
     * mirrored into availability counts — the seeder owns those rows and would reset
     * any decrement on the next run anyway.
     */
    private function seedDemoReservations(): void
    {
        $customers = [];
        foreach ([
            'sofia' => ['email' => 'sofia@example.test', 'first_name' => 'Sofia', 'last_name' => 'Andreou', 'phone' => '+30 690 000 0001'],
            'marco' => ['email' => 'marco@example.test', 'first_name' => 'Marco', 'last_name' => 'Bianchi', 'phone' => '+39 320 000 0002'],
            'claire' => ['email' => 'claire@example.test', 'first_name' => 'Claire', 'last_name' => 'Dubois', 'phone' => '+33 600 000 003'],
        ] as $key => $data) {
            $customers[$key] = Customer::updateOrCreate(
                ['email' => $data['email']],
                ['data' => ['first_name' => $data['first_name'], 'last_name' => $data['last_name'], 'repeat_email' => $data['email'], 'phone' => $data['phone']]],
            );
        }

        $spaRate = Rate::where('collection', 'spa_treatments')->where('slug', '90-min')->firstOrFail();
        $massageId = Entry::query()->where('collection', 'spa_treatments')->where('slug', 'mediterranean-massage')->first()->id();

        $reservations = [
            // A completed past stay — populates the reports/revenue views.
            'DEMO01' => [
                'status' => 'confirmed',
                'item_id' => $this->roomIds['sea-view-suite'],
                'rate' => $this->rates['best-flexible'],
                'date_start' => Carbon::today()->subDays(17),
                'date_end' => Carbon::today()->subDays(14),
                'quantity' => 1,
                'amount' => 960, // 3 nights x 320
                'customer' => $customers['sofia'],
                'created_at' => Carbon::now()->subDays(40),
                'updated_at' => Carbon::now()->subDays(40),
            ],
            // An upcoming confirmed stay — shows in the calendar/upcoming views.
            'DEMO02' => [
                'status' => 'confirmed',
                'item_id' => $this->roomIds['garden-view-double'],
                'rate' => $this->rates['best-flexible'],
                'date_start' => Carbon::today()->addDays(28),
                'date_end' => Carbon::today()->addDays(32),
                'quantity' => 1,
                'amount' => 720, // 4 nights x 180
                'customer' => $customers['marco'],
                'created_at' => Carbon::now()->subDays(5),
                'updated_at' => Carbon::now()->subDays(5),
            ],
            // A confirmed single-date spa appointment — multi-product reporting.
            'DEMO03' => [
                'status' => 'confirmed',
                'item_id' => $massageId,
                'rate' => $spaRate,
                'date_start' => Carbon::today()->addDays(10),
                'date_end' => Carbon::today()->addDays(11),
                'quantity' => 2,
                'amount' => 260, // 90-min couples pricing: 130 x 2
                'customer' => $customers['sofia'],
                'created_at' => Carbon::now()->subDays(3),
                'updated_at' => Carbon::now()->subDays(3),
            ],
            // EXPIRED + customer data + abandoned_email_sent_at NULL + stale updated_at:
            // the row resrv:send-abandoned-emails picks up.
            'DEMO04' => [
                'status' => 'expired',
                'item_id' => $this->roomIds['terracotta-villa'],
                'rate' => $this->rates['best-flexible'],
                'date_start' => Carbon::today()->addDays(35),
                'date_end' => Carbon::today()->addDays(38),
                'quantity' => 1,
                'amount' => 1920, // 3 nights x 640
                'customer' => $customers['claire'],
                'created_at' => Carbon::now()->subDays(2),
                'updated_at' => Carbon::now()->subDays(2),
            ],
        ];

        foreach ($reservations as $reference => $r) {
            Reservation::updateOrCreate(
                ['reference' => $reference],
                [
                    'status' => $r['status'],
                    'type' => 'normal',
                    'item_id' => $r['item_id'],
                    'date_start' => $r['date_start'],
                    'date_end' => $r['date_end'],
                    'quantity' => $r['quantity'],
                    'rate_id' => $r['rate']->id,
                    'cancellation_policy' => $r['rate']->cancellation_policy,
                    'free_cancellation_period' => $r['rate']->free_cancellation_period,
                    'price' => $r['amount'],
                    'payment' => $r['amount'],
                    'payment_surcharge' => 0,
                    // payment_id is NOT NULL in the schema — unpaid rows store ''.
                    'payment_id' => $r['status'] === 'confirmed' ? 'offline_demo_'.strtolower($reference) : '',
                    'payment_gateway' => $r['status'] === 'confirmed' ? 'offline' : null,
                    'total' => $r['status'] === 'confirmed' ? $r['amount'] : null,
                    'customer_id' => $r['customer']->id,
                    'abandoned_email_sent_at' => null,
                    'created_at' => $r['created_at'],
                    'updated_at' => $r['updated_at'],
                ],
            );
        }

        $this->command?->info('Demo reservations seeded (incl. one expired-unsent for the abandoned-recovery demo).');
    }

    /**
     * Single-date rolling availability (Appendix J): one row per open day, where
     * `available` is the day's bookable capacity (appointment slots / tables) and
     * `price` is per booking unit (spa appointment / restaurant table) — checkout
     * multiplies it by the search quantity (spa = guests; the restaurant books one
     * table, so quantity stays 1). Weekly closures are enforced in the data itself (no row
     * on the closed weekday) rather than via the search component's `disabledDays`,
     * so the engine refuses closed-day bookings regardless of front-end wiring.
     */
    private function seedSingleDateAvailability(string $entryId, int $rateId, int $price, int $available, int $closedWeekday): void
    {
        $start = Carbon::today()->startOfDay();
        $end = $start->copy()->addMonths(self::MONTHS_AHEAD);

        for ($date = $start->copy(); $date->lte($end); $date->addDay()) {
            if ($date->dayOfWeek === $closedWeekday) {
                continue;
            }

            Availability::updateOrCreate(
                ['statamic_id' => $entryId, 'date' => $date->toDateString(), 'rate_id' => $rateId],
                ['available' => $available, 'price' => $price],
            );
        }
    }
}
