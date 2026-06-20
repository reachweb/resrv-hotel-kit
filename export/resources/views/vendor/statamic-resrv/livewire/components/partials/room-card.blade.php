@use(Carbon\Carbon)
@use(Reach\StatamicResrv\Enums\CancellationPolicy)
@use(Illuminate\Support\Str)

{{-- Room card for the availability-collection grid. One shell, with a footer that
     adapts to the search state:
       • $row === null  → pre-search catalog card: the augmented "from" price
         ({{ availability:cheapest }}) + a link into the room. Lets the listing show
         every room before any dates are chosen.
       • $row available → live availability: per-rate Book rows when $showRates, else a
         single "from" price + Book button.
       • $row sold out  → "No availability" + a link into the room.

     Blade sibling of resources/views/partials/room-card.antlers.html (home showcase +
     "You may also like"); kept visually in sync so the room card reads the same across
     the site. $showRates / $data / $this->rateLabels are inherited from the component view. --}}

@php($entryUrl = $entry->url())
@php($hero = $entry->augmentedValue('hero_image')->value())
@php($hero = $hero instanceof \Illuminate\Support\Collection ? $hero->first() : $hero)
@php($available = $row['available'] ?? null)

{{-- Soft capacity hint: Resrv has no occupancy model, so the searched party size
     ($party, passed from the collection view) is compared against the room's
     max_occupancy content field purely to flag — never to block — undersized rooms. --}}
@php($party = $party ?? 0)
@php($maxOccupancy = (int) $entry->get('max_occupancy'))
@php($tooSmall = $available && $party > 0 && $maxOccupancy > 0 && $party > $maxOccupancy)

<article
    role="listitem"
    wire:key="resrv-collection-{{ $entry->id() }}"
    class="bg-white rounded-lg shadow-card overflow-hidden h-full flex flex-col transition-shadow duration-300 hover:shadow-raised @if($available === false) opacity-80 @endif"
>
    {{-- Image — links into the room; graceful placeholder when no hero is set (§0.4). --}}
    <a href="{{ $entryUrl }}" class="zoom-on-hover block">
        <div class="img-slot @if($hero) has-img @endif" style="aspect-ratio: 3/2;">
            @if ($hero)
                <img src="{{ $hero->manipulate(['w' => 1600]) }}" alt="{{ $entry->get('title') }}" loading="lazy">
            @else
                <span class="slot-label">Room image · 3:2</span>
            @endif
            @if ($available === false)
                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-semibold leading-none bg-sand text-muted absolute top-3 left-3 z-10">Sold out</span>
            @elseif ($tooSmall)
                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-semibold leading-none bg-warning/15 text-warning absolute top-3 left-3 z-10">Sleeps up to {{ $maxOccupancy }}</span>
            @endif
        </div>
    </a>

    <div class="p-5 lg:p-6 flex flex-col flex-1">
        <h3 class="t-h3 mb-1.5">
            <a href="{{ $entryUrl }}" class="hover:text-terracotta-dark transition-colors">{{ $entry->get('title') }}</a>
        </h3>

        @if ($entry->get('intro'))
            <p class="text-sm text-muted mb-4">{{ Str::limit(strip_tags((string) $entry->get('intro')), 96) }}</p>
        @endif

        <div class="flex flex-wrap gap-x-4 gap-y-1.5 text-sm text-muted mb-5">
            @if ($entry->get('size_sqm')) <span>{{ $entry->get('size_sqm') }} m²</span> @endif
            @if ($entry->get('max_occupancy')) <span>{{ $entry->get('max_occupancy') }} guests</span> @endif
            @if ($entry->get('bed_configuration')) <span>{{ $entry->get('bed_configuration') }}</span> @endif
        </div>

        <div class="mt-auto pt-4 border-t border-line">
            @if ($row === null)
                {{-- Pre-search: the lowest available rate across the rolling window. --}}
                @php($availability = $entry->augmentedValue('availability')->value())
                @php($cheapest = is_array($availability) ? data_get($availability, 'cheapest') : null)
                <div class="flex items-end justify-between gap-3">
                    @if ($cheapest)
                        <div>
                            <span class="text-sm text-muted">from</span>
                            <div class="font-display text-2xl text-terracotta-dark leading-none">{{ config('resrv-config.currency_symbol') }}{{ number_format((float) $cheapest, 0) }}<span class="text-sm text-muted font-sans"> / night</span></div>
                        </div>
                    @else
                        <span class="text-sm text-muted">Pick dates for rates</span>
                    @endif
                    <a href="{{ $entryUrl }}" class="inline-flex items-center justify-center gap-2 h-10 px-[18px] rounded-lg border-[1.5px] border-teal text-sm font-semibold leading-none whitespace-nowrap transition bg-transparent text-teal hover:bg-teal hover:text-white focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-terracotta shrink-0">View</a>
                </div>
            @elseif ($available)
                @if ($showRates)
                    <div class="flex flex-col gap-2.5">
                        @foreach ($row['rates'] as $rate)
                            @php($cancellation = data_get($rate, 'cancellation_policy'))
                            @php($cancellationLabel = $cancellation ? CancellationPolicy::labelFor($cancellation['policy'], $cancellation['period'], Carbon::parse($data->dates['date_start'])) : null)
                            <div class="flex items-center justify-between gap-3">
                                <div>
                                    <div class="text-sm font-medium">{{ data_get($this->rateLabels, data_get($rate, 'rate_id'), data_get($rate, 'rateLabel')) }}</div>
                                    <div class="font-display text-xl text-terracotta-dark leading-none">{{ config('resrv-config.currency_symbol') }}{{ data_get($rate, 'price') }}</div>
                                    @if ($cancellationLabel)<div class="text-sm text-success mt-0.5">{{ $cancellationLabel }}</div>@endif
                                </div>
                                <button
                                    type="button"
                                    class="inline-flex items-center justify-center gap-2 h-10 px-[18px] rounded-lg border-[1.5px] border-transparent text-sm font-semibold leading-none whitespace-nowrap transition bg-terracotta text-white hover:bg-terracotta-dark focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-terracotta shrink-0"
                                    wire:click="select('{{ $row['id'] }}', {{ data_get($rate, 'rate_id') ?? 'null' }})"
                                    aria-label="Book {{ data_get($this->rateLabels, data_get($rate, 'rate_id'), $entry->get('title')) }}"
                                >{{ trans('statamic-resrv::frontend.bookNow') }}</button>
                            </div>
                        @endforeach
                    </div>
                @else
                    @php($cancellation = data_get($row['from'], 'cancellation_policy'))
                    @php($cancellationLabel = $cancellation ? CancellationPolicy::labelFor($cancellation['policy'], $cancellation['period'], Carbon::parse($data->dates['date_start'])) : null)
                    <div class="flex items-end justify-between gap-3">
                        <div>
                            <span class="text-sm text-muted">from</span>
                            <div class="font-display text-2xl text-terracotta-dark leading-none">
                                @if (data_get($row['from'], 'original_price'))
                                    <span class="text-base text-muted line-through mr-1">{{ config('resrv-config.currency_symbol') }}{{ data_get($row['from'], 'original_price') }}</span>
                                @endif
                                {{ config('resrv-config.currency_symbol') }}{{ data_get($row['from'], 'price') }}
                            </div>
                            @if ($cancellationLabel)<div class="text-sm text-success mt-1">{{ $cancellationLabel }}</div>@endif
                        </div>
                        <button
                            type="button"
                            class="inline-flex items-center justify-center gap-2 h-10 px-[18px] rounded-lg border-[1.5px] border-transparent text-sm font-semibold leading-none whitespace-nowrap transition bg-terracotta text-white hover:bg-terracotta-dark focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-terracotta shrink-0"
                            wire:click="select('{{ $row['id'] }}', {{ data_get($row['from'], 'rate_id') ?? 'null' }})"
                            aria-label="Book {{ $entry->get('title') }}"
                        >{{ trans('statamic-resrv::frontend.bookNow') }}</button>
                    </div>
                @endif
            @else
                <div class="flex items-center justify-between gap-3">
                    <span class="text-sm text-muted">{{ trans('statamic-resrv::frontend.noAvailability') }}</span>
                    @if ($entryUrl)<a href="{{ $entryUrl }}" class="inline-flex items-center justify-center gap-2 h-10 px-[18px] rounded-lg border-[1.5px] border-teal text-sm font-semibold leading-none whitespace-nowrap transition bg-transparent text-teal hover:bg-teal hover:text-white focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-terracotta">View</a>@endif
                </div>
            @endif
        </div>
    </div>
</article>
