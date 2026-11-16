@use(Carbon\Carbon)

{{-- The spa menu as Resrv's availability-collection, via
     view="availability-collection-treatments" (partials/blocks/treatments_grid).
     Restyled from the stock availability-collection view, with one deliberate change:
     stock books each rate with a wire:click="select(entry, rate)" button. select()
     writes that rate into the shared resrv-search session before it opens the entry's
     page, and the treatment page's results then collapse to that one duration instead
     of showing every duration side by side (the restaurant's availability-list-by-date
     calls selectDate() with the date only, for the same reason). So each card links to
     its treatment page instead, and the session carries only the day. The $data /
     $this->rows / $this->resolvedEntries structure is stock.
     The card is components/partials/treatment-card, one shell in both states (image,
     prices, the appointment times picked at checkout, "View & book"), so a search only
     swaps the price rows:
       • before a day is picked → every treatment as a catalog card with its "from"
         price, so the menu is never an empty "add a date" prompt;
       • after → the treatments available that day, each with its durations' live
         prices. The ones that aren't available (showUnavailable="true") are named under
         the grid, so a missing treatment never looks like a missing menu item;
       • nothing available → one notice (the spa is closed that day, or fully booked),
         left-aligned at a reading width above the catalog cards, so the menu stays on
         the page.
     The cards say the time is picked at checkout, so the intro doesn't repeat it.
     Closed days come from the tag's closed-days="Tuesday" (English weekday names, from
     the treatments_grid set's "Closed days"; Livewire forwards it to this view as an
     HTML attribute). They name the closed days once in the summary line and tell a
     closed day from a fully booked one. Without them, an empty day reads as fully booked.
     The summary line (intro, count or notice) is one role="status" region that stays in
     place across searches, so a screen reader hears the result after a day is picked.
     Its two sentences are inline-blocks: side by side when they fit, otherwise each
     starts its own line, so a wrap never leaves "on Tuesdays." or "prices." alone.
     While a search loads, the grid wrapper is aria-busy and one sr-only status (outside
     it, so it isn't held back) says "Updating availability…". Errors are role="alert". --}}
@php
    $closedDays = array_values(array_filter(array_map('trim', explode(',', (string) $attributes->get('closed-days')))));
    $closedDaysLabel = collect($closedDays)->map(fn ($weekday) => $weekday.'s')->join(', ', ' and ');
    $dayLabel = fn (Carbon $date) => $date->format($date->isCurrentYear() ? 'l j F' : 'l j F Y');

    $available = collect();
    $taken = collect();
    $isClosedDay = false;
    $reopensOn = null;

    if ($data->hasDates()) {
        $date = Carbon::parse($data->dates['date_start']);
        $day = $dayLabel($date);
        $available = $this->rows->where('available', true);
        $taken = $this->rows->where('available', false);
        $isClosedDay = in_array($date->format('l'), $closedDays, true);

        if ($isClosedDay && count($closedDays) < 7) {
            $reopens = $date->copy()->addDay();
            while (in_array($reopens->format('l'), $closedDays, true)) {
                $reopens->addDay();
            }
            $reopensOn = $dayLabel($reopens);
        }
    }

    $showMenu = ! $data->hasDates() || $available->isEmpty();
@endphp
<div class="relative">
    <div wire:loading.delay.long.attr="aria-busy">
    <div role="status">
        @if (! $data->hasDates())
            <p class="text-ui text-ink/80 text-pretty max-w-2xl mb-6 lg:mb-8"><span class="inline-block">Add a date to see which treatments are available, with that day's prices.</span>@if ($closedDaysLabel) <span class="inline-block">The spa is closed on {{ $closedDaysLabel }}.</span>@endif</p>
        @elseif ($available->isNotEmpty())
            <p class="text-ui text-ink/80 text-pretty mb-6 lg:mb-8"><span class="inline-block">{{ trans_choice(':count treatment|:count treatments', $available->count()) }} available on <span class="font-semibold text-ink whitespace-nowrap">{{ $day }}</span>.</span>@if ($closedDaysLabel) <span class="inline-block">The spa is closed on {{ $closedDaysLabel }}.</span>@endif</p>
        @elseif (! $errors->has('availability'))
            <div class="flex items-start gap-4 max-w-2xl mb-8 lg:mb-10">
                <span class="grid place-items-center size-10 shrink-0 rounded-full bg-white text-terracotta-dark" aria-hidden="true">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" class="size-5"><path d="M8 2v3M16 2v3M3 9h18M5 5h14a2 2 0 0 1 2 2v12a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V7a2 2 0 0 1 2-2Z"/><path d="m9.5 13.5 5 5M14.5 13.5l-5 5"/></svg>
                </span>
                <div class="min-w-0 pt-1.5">
                    @if ($isClosedDay)
                        <p class="t-h3 mb-1.5 text-balance">Closed on {{ $day }}</p>
                        <p class="text-ui text-ink/80 text-pretty">The spa is closed on {{ $closedDaysLabel }}.@if ($reopensOn) It opens again on {{ $reopensOn }}.@endif</p>
                    @else
                        <p class="t-h3 mb-1.5 text-balance">Nothing available on {{ $day }}</p>
                        <p class="text-ui text-ink/80 text-pretty">The spa is fully booked that day. Try another date.</p>
                    @endif
                </div>
            </div>
        @endif
    </div>

    @if ($errors->has('availability') && $data->hasDates())
        <div role="alert" class="rounded-lg border border-error/30 bg-error/5 p-4 mb-6">
            <p class="text-sm font-medium text-error">{{ trans('statamic-resrv::frontend.searchError') }}</p>
            <p class="text-sm text-muted">{{ $errors->first('availability') }}</p>
        </div>
    @endif

    @if ($showMenu)
        <div class="grid gap-5 md:grid-cols-2 lg:grid-cols-3" role="list">
            @foreach ($this->resolvedEntries as $entry)
                @include('statamic-resrv::livewire.components.partials.treatment-card', ['entry' => $entry, 'row' => null])
            @endforeach
        </div>
    @else
        <div class="grid gap-5 md:grid-cols-2 lg:grid-cols-3" role="list">
            @foreach ($available as $row)
                @include('statamic-resrv::livewire.components.partials.treatment-card', ['entry' => $row['entry'], 'row' => $row])
            @endforeach
        </div>
        @if ($taken->isNotEmpty())
            <p class="mt-6 text-sm text-muted">
                Not available that day:
                @foreach ($taken as $row)
                    <a href="{{ $row['entry']->url() }}" class="font-medium text-ink underline decoration-line-strong underline-offset-4 hover:text-terracotta-dark hover:decoration-current transition-colors">{{ $row['entry']->get('title') }}</a>{{ $loop->last ? '.' : ',' }}
                @endforeach
                Open one to see the days it's available.
            </p>
        @endif
    @endif
    </div>

    <div role="status" class="sr-only"><span wire:loading.delay.long>Updating availability…</span></div>
    <div class="absolute inset-0 bg-sand/50" wire:loading.delay.long aria-hidden="true">
        <span class="flex h-full w-full items-center justify-center">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6 animate-spin text-teal" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0 3.181 3.183a8.25 8.25 0 0 0 13.803-3.7M4.031 9.865a8.25 8.25 0 0 1 13.803-3.7l3.181 3.182m0-4.991v4.99" />
            </svg>
        </span>
    </div>
</div>
