{{-- Treatment card for the spa's availability-collection grid
     (availability-collection-treatments). One shell for both states, so picking a day
     changes the prices and little else (the grid barely moves):
       image (the entry's `image`, or the named empty slot) · title · summary · prices ·
       the for-two note · the appointment times · "View & book".
     Only the price block differs:
       • $row === null → before a day is picked, or when nothing is available that day:
         one row with the duration label and the augmented "from" price.
       • $row available → the day's live prices, one row per duration (Resrv rate) and
         in whole units like the treatment page's results panel.
     Blade sibling of resources/views/partials/treatment-card.antlers.html (the treatment
     page's "More from the menu"), which is the catalog state; keep the two in sync.

     The whole card opens the treatment's page: the title is the one link (one tab stop,
     named by the title) and its ::after covers the card. "View & book" is a visual cue
     inside that target, hidden from assistive tech, since a second link to the same page
     would only double the tab stops. Keyboard focus rings the whole card. It stays a
     plain link, not stock's per-rate select() button (the collection view says why): the
     shared resrv-search session carries the day to that page, whose search clears any
     rate, so its results show every duration side by side.

     Appointment times are the values of the entry's `appointment-time` Resrv Option
     (read through its augmented `options` field). They are the same on every open day,
     so both states show them. The guest picks one at checkout; Resrv doesn't track
     availability per time, so they show as information under a visible "Pick your time
     at checkout" label, never as available slots. The line is left out when the option
     doesn't exist. A no-break space before each " · " keeps the dot off the start of a
     wrapped line.
     A treatment with `for_two` (the Couples Ritual) says in both states that it is one
     appointment for two, priced for both, in the same words as the treatment page's
     booking panel; without the field, nothing shows. The no-break spaces keep a wrap
     at the comma ("One appointment for two, / priced for both.") on a narrow card.
     The image is decorative (alt="", unless the asset has its own alt text): the title
     beside it names the treatment. $loop comes from the grid's @foreach and moves the
     empty slot's light per card. $this->rateLabels is inherited from the component
     view (rates="true"). --}}

@php
    $entryUrl = $entry->url();
    $title = $entry->get('title');
    $forTwo = (bool) $entry->get('for_two');

    $image = $entry->augmentedValue('image')->value();
    $image = $image instanceof \Illuminate\Support\Collection ? $image->first() : $image;
    $imageWidth = $image ? ((int) $image->width() ?: 2400) : 0;
    $srcset = collect([480, 800, 1200])
        ->filter(fn ($width) => $width === 480 || $imageWidth >= $width)
        ->map(fn ($width) => $image?->manipulate(['w' => $width, 'fit' => 'max', 'fm' => 'webp']).' '.$width.'w')
        ->implode(', ');

    $options = $entry->augmentedValue('options')->value();
    $times = collect(data_get($options, 'appointment-time.values', []))->where('published', true)->pluck('name');

    if ($row === null) {
        $availability = $entry->augmentedValue('availability')->value();
        $cheapest = is_array($availability) ? data_get($availability, 'cheapest') : null;
    }
@endphp

<div role="listitem" wire:key="resrv-treatment-{{ $entry->id() }}" class="h-full">
    <article class="group zoom-on-hover relative bg-white rounded-lg overflow-hidden h-full flex flex-col transition-shadow duration-300 hover:shadow-raised has-[a:focus-visible]:outline-2 has-[a:focus-visible]:outline-offset-2 has-[a:focus-visible]:outline-terracotta-dark">
        <div @class(['img-slot aspect-3/2 rounded-none', 'has-img' => $image, 'tone-sage slot-v'.((isset($loop) ? $loop->index : 0) % 3 + 1) => ! $image])>
            @if ($image)
                <img src="{{ $image->manipulate(['w' => 800, 'fit' => 'max', 'fm' => 'webp']) }}" srcset="{{ $srcset }}" sizes="(min-width: 1280px) 387px, (min-width: 1024px) 31vw, (min-width: 768px) 47vw, calc(100vw - 48px)" alt="{{ $image->get('alt') ?? '' }}" loading="lazy" decoding="async">
            @else
                <span class="slot-label" aria-hidden="true">Treatment image<span>3:2</span></span>
            @endif
        </div>

        <div class="flex flex-col flex-1 p-5 lg:p-6">
            <h3 class="t-h3 mb-1.5"><a href="{{ $entryUrl }}" class="transition-colors group-hover:text-terracotta-dark focus-visible:outline-none max-lg:scroll-mt-20 after:absolute after:inset-0">{{ $title }}</a></h3>
            @if ($entry->get('summary'))<p class="text-sm text-muted text-pretty">{{ $entry->get('summary') }}</p>@endif

            <div class="mt-auto pt-5">
                @if ($row === null)
                    <div class="flex items-baseline justify-between gap-3 py-2.5 border-y border-line">
                        @if ($entry->get('duration_options'))<span class="min-w-0 text-sm font-medium">{{ $entry->get('duration_options') }}</span>@endif
                        @if ($cheapest)<span class="ml-auto shrink-0"><span class="text-sm text-muted">from <span class="font-display text-xl text-terracotta-dark">{{ config('resrv-config.currency_symbol') }}{{ number_format((float) $cheapest, 0) }}</span></span></span>@endif
                    </div>
                @else
                    <ul class="border-y border-line divide-y divide-line">
                        @foreach ($row['rates'] as $rate)
                            <li class="flex items-baseline justify-between gap-3 py-2.5">
                                <span class="min-w-0 text-sm font-medium">{{ data_get($this->rateLabels, data_get($rate, 'rate_id'), data_get($rate, 'rateLabel')) }}</span>
                                <span class="shrink-0 font-display text-xl text-terracotta-dark tabular-nums whitespace-nowrap">
                                    @if (data_get($rate, 'original_price'))<span class="text-sm text-muted line-through mr-1">{{ config('resrv-config.currency_symbol') }}{{ number_format((float) data_get($rate, 'original_price'), 0) }}</span>@endif{{ config('resrv-config.currency_symbol') }}{{ number_format((float) data_get($rate, 'price'), 0) }}
                                </span>
                            </li>
                        @endforeach
                    </ul>
                @endif

                @if ($forTwo)
                    <p class="flex items-start gap-2 py-3 border-b border-line text-sm text-ink/85">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" class="size-4 mt-0.5 shrink-0 text-sage" aria-hidden="true"><circle cx="9" cy="8" r="3.5"/><path d="M2.5 20v-1a5 5 0 0 1 5-5h3a5 5 0 0 1 5 5v1"/><path d="M15.5 4.6a3.5 3.5 0 0 1 0 6.8"/><path d="M18.5 14.2a5 5 0 0 1 3 4.8v1"/></svg>
                        <span class="text-pretty">One appointment for&nbsp;two, priced for&nbsp;both.</span>
                    </p>
                @endif

                @if ($times->isNotEmpty())
                    <p class="flex items-start gap-2 pt-3 text-sm">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" class="size-4 mt-0.5 shrink-0 text-sage" aria-hidden="true"><circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/></svg>
                        <span><span class="block text-muted">Pick your time at checkout<span class="sr-only">:</span></span><span class="block tabular-nums text-ink/85">{{ $times->implode("\u{00A0}· ") }}</span></span>
                    </p>
                @endif

                <span class="mt-4 inline-flex items-center gap-1.5 text-sm font-semibold text-teal transition-colors group-hover:text-terracotta-dark" aria-hidden="true">View &amp; book<svg class="size-4 transition-transform motion-safe:group-hover:translate-x-0.75" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6"><path d="m9 18 6-6-6-6"/></svg></span>
            </div>
        </div>
    </article>
</div>
