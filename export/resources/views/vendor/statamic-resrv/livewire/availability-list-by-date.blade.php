@use(Carbon\Carbon)

{{-- Restyled "pick another day" browser (La Marea). Markup/classes only — the
     selectDate() wiring is stock. Kit hardening: the list is aria-busy while it loads
     (no status message of its own: the results panel on the same page announces the
     update), errors are role="alert", and the scroll back to the panel is instant
     under reduced motion.

     Each day is one row: the day, then every sitting with its price, or "full" when it
     has no tables left that day (`$rateEntries` holds only the rates with availability;
     `$this->entryRates` names them all, in their Resrv order — the widget passes
     rates="true"). The rows share the list's grid (subgrid), so the date column is as
     wide as the longest date and every row's sittings start in the same place. A row
     is three cells (day · sittings · chevron) in `$rowClass`, so a row that isn't a
     button (a closed day, say) can take the same columns.

     Each day's button is named in one clean phrase ("Thu 3 Dec, Lunch Sitting €35,
     Dinner Sitting full"): from its content, the dot drawn between the sittings ran
     into the price ("€35· Dinner").

     Optional closed weekdays: a venue's "Closed days" arrive on the tag as
     closed-days="Monday" (La Marea's band; comma-separated English day names). It
     isn't a property, so Livewire forwards it to this view as an HTML attribute. Within
     the days shown, each closed weekday gets its own "Closed" row in date order: the
     same columns, no price, and not a button. A searched day that is closed (a ?date=
     link) heads the list as the current day. The engine leaves closed days out (they
     have no availability), so without the attribute the list skips them, as before.
     The heading is the kit's `daysAhead` ("The days ahead"), not stock "Available
     dates": the list holds closed days too. --}}
@php
    $currency = config('resrv-config.currency_symbol');
    $selectedDate = $data->hasDates() ? Carbon::parse($data->dates['date_start']) : null;
    $rowClass = 'col-span-3 grid grid-cols-subgrid items-center py-3.5';
    $closedDays = array_values(array_filter(array_map('trim', explode(',', (string) $attributes->get('closed-days')))));

    // Display cap: the engine returns every future date with availability from the
    // chosen day on. Show the next 7; the calendar above reaches the rest. A closed
    // day in that span is a row of its own (null: no sittings).
    $days = $availableDates->take(7);
    if ($closedDays && $days->isNotEmpty()) {
        $first = Carbon::parse($days->keys()->first());
        $last = Carbon::parse($days->keys()->last());
        $from = ($selectedDate?->lt($first) && in_array($selectedDate->format('l'), $closedDays, true)) ? $selectedDate->copy() : $first;
        $shown = $days;
        $days = collect();
        for ($day = $from->startOfDay(); $day->lte($last); $day->addDay()) {
            $key = $day->toDateString();
            if ($shown->has($key)) {
                $days->put($key, $shown->get($key));
            } elseif (in_array($day->format('l'), $closedDays, true)) {
                $days->put($key, null);
            }
        }
    }
@endphp
<div class="relative">
    <div wire:loading.delay.long.attr="aria-busy">
    @if ($availableDates->isNotEmpty())
        <h3 class="text-xl font-semibold">{{ trans('statamic-resrv::frontend.daysAhead') }}</h3>
        <p class="text-sm text-muted mt-0.5">{{ trans('statamic-resrv::frontend.availableDatesHelp') }}</p>

        <ul role="list" class="grid grid-cols-[auto_1fr_auto] gap-x-4 mt-2 divide-y divide-line">
            @foreach ($days as $date => $rateEntries)
                @php($day = Carbon::parse($date))
                @php($isSelected = $selectedDate?->isSameDay($day) ?? false)
                @if ($rateEntries === null)
                <li class="{{ $rowClass }}" wire:key="day-{{ $date }}" @if ($isSelected) aria-current="date" @endif>
                    <span class="text-ui font-medium whitespace-nowrap">
                        <span class="text-muted">{{ $day->format('D') }}</span>
                        <span class="{{ $isSelected ? 'text-terracotta-dark' : 'text-muted' }}">{{ $day->format('j M') }}</span>
                    </span>
                    <span class="text-sm text-muted">{{ trans('statamic-resrv::frontend.closedDay') }}</span>
                </li>
                @else
                @php($rowLabel = $day->format('D j M').', '.collect($this->entryRates)->map(fn ($rateTitle, $rateId) => $rateTitle.' '.(isset($rateEntries[$rateId]) ? $currency.round((float) $rateEntries[$rateId]['price']) : trans('statamic-resrv::frontend.rateFull')))->join(', '))
                <li class="col-span-3 grid grid-cols-subgrid" wire:key="day-{{ $date }}">
                    {{-- x-on:click (not @click — Blade would parse @ as a directive) smoothly
                         (instantly under reduced motion) brings the search/results panel back
                         into view; block:'nearest' makes it a no-op when the panel is already
                         visible, so it only scrolls when it makes sense (e.g. you're scrolled
                         down at the list on mobile). The target sits above the dynamic
                         results/list, so its position is morph-stable. --}}
                    <button type="button"
                            wire:click="selectDate('{{ $date }}')"
                            x-on:click="document.querySelector('[data-resrv-booking-top]')?.scrollIntoView({ behavior: matchMedia('(prefers-reduced-motion: reduce)').matches ? 'auto' : 'smooth', block: 'nearest' })"
                            @if ($isSelected) aria-current="date" @endif
                            aria-label="{{ $rowLabel }}"
                            class="group {{ $rowClass }} text-left cursor-pointer">
                        <span class="text-ui font-medium whitespace-nowrap">
                            <span class="text-muted">{{ $day->format('D') }}</span>
                            <span class="{{ $isSelected ? 'text-terracotta-dark' : 'group-hover:text-terracotta-dark transition-colors' }}">{{ $day->format('j M') }}</span>
                        </span>
                        {{-- In a narrow card (under @md, a phone) the sittings stack; wider, they
                             share a line with a dot between them, wrapping between sittings and
                             never inside one. The dot trails each sitting, so a wrapped line never
                             starts with it. --}}
                        <span class="flex flex-col gap-y-0.5 text-sm text-muted @md:flex-row @md:flex-wrap @md:gap-x-2 @md:[&>span:not(:last-child)]:after:content-['·'] @md:[&>span:not(:last-child)]:after:ms-2">
                            @foreach ($this->entryRates as $rateId => $rateTitle)
                                <span class="whitespace-nowrap">{{ $rateTitle }} @if (isset($rateEntries[$rateId]))<span class="font-medium text-ink tabular-nums">{{ $currency }}{{ round((float) $rateEntries[$rateId]['price']) }}</span>@else{{ trans('statamic-resrv::frontend.rateFull') }}@endif</span>
                            @endforeach
                        </span>
                        <svg class="size-4 text-muted motion-safe:group-hover:translate-x-0.5 transition-transform" aria-hidden="true" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="m9 18 6-6-6-6"/></svg>
                    </button>
                </li>
                @endif
            @endforeach
        </ul>
    @elseif ($data->hasDates())
        <div class="py-2">
            <p class="font-medium">{{ trans('statamic-resrv::frontend.noAvailableDates') }}</p>
            <p class="text-sm text-muted mt-0.5">{{ trans('statamic-resrv::frontend.tryAdjustingYourSearch') }}</p>
        </div>
    @else
        {{-- No date yet: the results panel above already asks for one, so the day
             browser stays empty instead of repeating the prompt. The marker lets the
             wrapping section hide itself too (has-[[data-awaiting-date]]:hidden). --}}
        <div data-awaiting-date></div>
    @endif

    @if ($errors->has('availability') && $data->hasDates())
    <div role="alert" class="rounded-lg border border-error/30 bg-error/5 p-4 mt-4">
        <p class="text-sm font-medium text-error">{{ trans('statamic-resrv::frontend.searchError') }}</p>
        <p class="text-sm text-muted">{{ $errors->first('availability') }}</p>
    </div>
    @endif

    </div>

    <div class="absolute inset-0 bg-white/60" wire:loading.delay.long aria-hidden="true">
        <span class="flex items-center justify-center w-full h-full">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="animate-spin size-5 text-teal" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0 3.181 3.183a8.25 8.25 0 0 0 13.803-3.7M4.031 9.865a8.25 8.25 0 0 1 13.803-3.7l3.181 3.182m0-4.991v4.99" />
            </svg>
        </span>
    </div>
</div>
