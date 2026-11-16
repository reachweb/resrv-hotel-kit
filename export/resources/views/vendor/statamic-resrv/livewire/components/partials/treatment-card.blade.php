{{-- Treatment card for the spa's availability-collection grid
     (availability-collection-treatments). Two states:
       • $row === null → before a day is picked: the catalog card, a link into the
         treatment with its duration label and augmented "from" price. Blade sibling of
         resources/views/partials/treatment-card.antlers.html; keep the two in sync.
       • $row available → the day's live prices, one line per duration (Resrv rate) and
         in whole units like the treatment page's results panel, then the appointment
         times and Book. Book is a plain link to the treatment's page:
         the shared resrv-search session carries the day there, and that page's search
         clears any rate, so its results show every duration side by side anyway.
     Appointment times are the values of the entry's `appointment-time` Resrv Option
     (read through its augmented `options` field). The guest picks one at checkout;
     Resrv doesn't track availability per time, so they show as information, not as
     choices. The line is left out when the option doesn't exist. A no-break space
     before each " · " keeps the dot off the start of a wrapped line.
     $this->rateLabels is inherited from the component view (rates="true"). --}}

@php($entryUrl = $entry->url())
@php($title = $entry->get('title'))

<div role="listitem" wire:key="resrv-treatment-{{ $entry->id() }}" class="h-full">
@if ($row === null)
    @php($availability = $entry->augmentedValue('availability')->value())
    @php($cheapest = is_array($availability) ? data_get($availability, 'cheapest') : null)
    <a href="{{ $entryUrl }}" class="group block h-full">
        <article class="bg-white rounded-lg p-6 h-full flex flex-col transition-shadow duration-300 group-hover:shadow-raised">
            <div class="text-muted mb-4">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" class="size-6" aria-hidden="true"><path d="M7 21c2.5-1.5 4-4 4-7V4a8 8 0 0 1 4 7c0 2-1 4-2 5"/><path d="M11 21c4 0 8-2.5 8-8 0-2-1-4-2-5"/><path d="M3 13c0 4.5 3 8 8 8"/></svg>
            </div>
            <h3 class="t-h3 mb-1.5 group-hover:text-terracotta-dark transition-colors">{{ $title }}</h3>
            @if ($entry->get('summary'))<p class="text-sm text-muted mb-5 flex-1">{{ $entry->get('summary') }}</p>@endif
            <div class="mt-auto flex items-center justify-between pt-4 border-t border-line">
                @if ($entry->get('duration_options'))<span class="text-sm text-muted">{{ $entry->get('duration_options') }}</span>@endif
                @if ($cheapest)<span class="text-sm text-muted">from <span class="font-display text-xl text-terracotta-dark">{{ config('resrv-config.currency_symbol') }}{{ number_format((float) $cheapest, 0) }}</span></span>@endif
            </div>
        </article>
    </a>
@else
    @php($options = $entry->augmentedValue('options')->value())
    @php($times = collect(data_get($options, 'appointment-time.values', []))->where('published', true)->pluck('name'))
    <article class="bg-white rounded-lg p-6 h-full flex flex-col transition-shadow duration-300 hover:shadow-raised">
        <div class="text-muted mb-4">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" class="size-6" aria-hidden="true"><path d="M7 21c2.5-1.5 4-4 4-7V4a8 8 0 0 1 4 7c0 2-1 4-2 5"/><path d="M11 21c4 0 8-2.5 8-8 0-2-1-4-2-5"/><path d="M3 13c0 4.5 3 8 8 8"/></svg>
        </div>
        <h3 class="t-h3 mb-1.5"><a href="{{ $entryUrl }}" class="hover:text-terracotta-dark transition-colors">{{ $title }}</a></h3>
        @if ($entry->get('summary'))<p class="text-sm text-muted mb-5">{{ $entry->get('summary') }}</p>@endif

        <div class="mt-auto">
            <ul class="border-t border-line divide-y divide-line">
                @foreach ($row['rates'] as $rate)
                    <li class="flex items-baseline justify-between gap-3 py-2.5">
                        <span class="text-sm font-medium">{{ data_get($this->rateLabels, data_get($rate, 'rate_id'), data_get($rate, 'rateLabel')) }}</span>
                        <span class="font-display text-xl leading-none text-terracotta-dark tabular-nums whitespace-nowrap">
                            @if (data_get($rate, 'original_price'))<span class="text-sm text-muted line-through mr-1">{{ config('resrv-config.currency_symbol') }}{{ number_format((float) data_get($rate, 'original_price'), 0) }}</span>@endif{{ config('resrv-config.currency_symbol') }}{{ number_format((float) data_get($rate, 'price'), 0) }}
                        </span>
                    </li>
                @endforeach
            </ul>

            @if ($times->isNotEmpty())
                <p class="flex items-start gap-2 pt-3 border-t border-line text-sm text-ink/85">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" class="size-4 mt-0.5 shrink-0 text-sage" aria-hidden="true"><circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/></svg>
                    <span><span class="sr-only">Appointment times: </span><span class="tabular-nums">{{ $times->implode("\u{00A0}· ") }}</span></span>
                </p>
            @endif

            <x-resrv::kit-cta size="sm" full :href="$entryUrl" class="mt-4">Book<span class="sr-only">: {{ $title }}</span></x-resrv::kit-cta>
        </div>
    </article>
@endif
</div>
