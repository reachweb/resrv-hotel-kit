@use(Carbon\Carbon)

{{-- Restyled to the Resrv Hotel design (rate rows live in
     components/availability-results-advanced). Markup/classes only — all
     wire: directives and the component structure are preserved.

     Kit hardening (keep through 3-way merges on upgrade):
     · The results sit in an inner wrapper that is aria-busy while a search loads; the
       one "Updating availability…" status lives outside it, since a screen reader may
       hold back live updates inside a busy region.
     · Errors are role="alert", so they are announced when a Livewire update adds them.
     · Messages use <p> (stock had bare dt/dd outside a dl).
     · Rooms search a date range; spa treatments and La Marea tables search one day. The
       summary and the empty prompt follow that (`$isStay`), so a one-day search never
       asks for "dates".
     · The rate list names the product's rates the way a guest would: a spa treatment's
       are durations (`pleaseSelectDuration`), La Marea's are sittings
       (`pleaseSelectSitting`), and rooms keep "Choose a rate". A table's price is per
       table (`perTable`, after each sitting's price); rooms show "/ night" for a stay of
       more than one night, and treatments need no unit.
     · The one-day summary shows the day unpadded ("Thu 3 Dec 2026"), like the fields.
     · The prompt before a search is a plain line, not a tinted box inside the white panel.
     · Optional closed weekdays: a venue's "Closed days" arrive on the tag as
       closed-days="Monday" (La Marea's band; comma-separated English day names). It
       isn't a property, so Livewire forwards it to this view as an HTML attribute. When
       the searched day falls on one (a ?date= link or a session date; the calendar can't
       pick it), the panel says the venue is closed that day and when it opens again,
       instead of every sitting reading "No availability", like the spa's closed day.
       The day list under the band stays the way on. Without the attribute (rooms,
       treatments) nothing changes. --}}
@php
    $closedDays = array_values(array_filter(array_map('trim', explode(',', (string) $attributes->get('closed-days')))));
    $searchDay = $data->hasDates() ? Carbon::parse($data->dates['date_start']) : null;
    $isClosedDay = $searchDay && in_array($searchDay->format('l'), $closedDays, true);
    if ($isClosedDay) {
        $dayLabel = fn (Carbon $date) => $date->format($date->isCurrentYear() ? 'l j F' : 'l j F Y');
        $closedDaysLabel = collect($closedDays)->map(fn ($weekday) => $weekday.'s')->join(', ', ' and ');
        $reopensOn = null;
        if (count($closedDays) < 7) {
            $reopens = $searchDay->copy()->addDay();
            while (in_array($reopens->format('l'), $closedDays, true)) {
                $reopens->addDay();
            }
            $reopensOn = $dayLabel($reopens);
        }
    }
@endphp
@php($collectionHandle = $this->entry?->collection()?->handle())
@php($isStay = $collectionHandle === 'rooms')
@php($selectDatesPrompt = trans($isStay ? 'statamic-resrv::frontend.pleaseSelectDates' : 'statamic-resrv::frontend.pleaseSelectDay'))
@php($rateListLabel = trans(match ($collectionHandle) {
    'spa_treatments' => 'statamic-resrv::frontend.pleaseSelectDuration',
    'restaurant' => 'statamic-resrv::frontend.pleaseSelectSitting',
    default => 'statamic-resrv::frontend.pleaseSelectRateToBook',
}))
@php($priceUnit = $collectionHandle === 'restaurant' ? trans('statamic-resrv::frontend.perTable') : null)
<div class="relative">
    <div wire:loading.delay.long.attr="aria-busy">
    @if ($isClosedDay)
    <div class="flex items-start gap-3 my-3">
        <span class="grid place-items-center size-10 shrink-0 rounded-full bg-sand text-terracotta-dark" aria-hidden="true">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" class="size-5"><path d="M8 2v3M16 2v3M3 9h18M5 5h14a2 2 0 0 1 2 2v12a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V7a2 2 0 0 1 2-2Z"/><path d="m9.5 13.5 5 5M14.5 13.5l-5 5"/></svg>
        </span>
        <div class="min-w-0 pt-1.5">
            <p class="t-h3 mb-1.5 text-balance">{{ trans('statamic-resrv::frontend.closedOnDay', ['day' => $dayLabel($searchDay)]) }}</p>
            <p class="text-sm text-muted text-pretty">{{ trans('statamic-resrv::frontend.venueClosedOn', ['venue' => $this->entry?->get('title'), 'days' => $closedDaysLabel]) }}@if ($reopensOn) {{ trans('statamic-resrv::frontend.venueOpensAgainOn', ['day' => $reopensOn]) }}@endif</p>
        </div>
    </div>
    @elseif ($rates == true && (! $data->rate || $data->rate === 'any'))
    <x-resrv::availability-results-advanced :$availability :entryRates="$this->entryRates" :dateStart="$data->dates['date_start'] ?? null" :emptyMessage="$selectDatesPrompt" :label="$rateListLabel" :unit="$priceUnit" />
    @else
        @if (data_get($availability, 'message.status') === true)
            @if ($this->showOptions)
            <div class="flex flex-col gap-y-6 my-4">
                <livewire:options
                    :data="$this->data"
                    :filter="$this->showOptions"
                    :entryId="$this->entry->id"
                    :effectiveRateId="$this->effectiveRateId()"
                />
            </div>
            @endif
            @if ($this->showExtras)
            <div class="flex flex-col gap-y-6 my-4">
                <livewire:extras
                    :data="$this->data"
                    :filter="$this->showExtras"
                    :entryId="$this->entry->id"
                    :effectiveRateId="$this->effectiveRateId()"
                />
            </div>
            @endif
        <div class="divide-y divide-line">
            <div class="flex flex-col pb-5">
                <div class="font-medium mb-2">{{ trans('statamic-resrv::frontend.yourSearch') }}</div>
                <dl class="text-sm space-y-1">
                    @if ((int) data_get($availability, 'request.days', 0) > 1 || $isStay)
                    <div class="flex flex-wrap gap-x-1.5">
                        <dt class="text-muted">{{ ucfirst(trans('statamic-resrv::frontend.from')) }}:</dt>
                        <dd class="font-medium">{{ Carbon::parse($data->dates['date_start'])->format('D d M Y') }}</dd>
                    </div>
                    <div class="flex flex-wrap gap-x-1.5">
                        <dt class="text-muted">{{ ucfirst(trans('statamic-resrv::frontend.to')) }}:</dt>
                        <dd class="font-medium">{{ Carbon::parse($data->dates['date_end'])->format('D d M Y') }}</dd>
                    </div>
                    <div class="flex flex-wrap gap-x-1.5">
                        <dt class="text-muted">{{ ucfirst(trans('statamic-resrv::frontend.duration')) }}:</dt>
                        <dd class="font-medium">{{ data_get($availability, 'request.days') }} {{ trans('statamic-resrv::frontend.days') }}</dd>
                    </div>
                    @else
                    {{-- Single-date products (spa, restaurant): one date, no checkout range --}}
                    <div class="flex flex-wrap gap-x-1.5">
                        <dt class="text-muted">{{ ucfirst(trans('statamic-resrv::frontend.date')) }}:</dt>
                        <dd class="font-medium">{{ Carbon::parse($data->dates['date_start'])->format('D j M Y') }}</dd>
                    </div>
                    @if ($data->quantity > 1)
                    <div class="flex flex-wrap gap-x-1.5">
                        <dt class="text-muted">{{ trans('statamic-resrv::frontend.quantityLabel') }}:</dt>
                        <dd class="font-medium">× {{ $data->quantity }}</dd>
                    </div>
                    @endif
                    @endif
                </dl>
            </div>
            <div class="flex flex-col py-5">
                @include('statamic-resrv::livewire.components.partials.availability-results-pricing')
            </div>
        </div>
        <div class="mt-5">
            <x-resrv::kit-cta full wire:click="checkout()">{{ trans('statamic-resrv::frontend.bookNow') }}</x-resrv::kit-cta>
        </div>
        @elseif (data_get($availability, 'message.status') === false)
        <div class="rounded-lg bg-sand/60 p-5 text-center">
            <p class="t-h3 mb-1">{{ trans('statamic-resrv::frontend.noAvailability') }}</p>
            <p class="text-sm text-muted">{{ trans('statamic-resrv::frontend.tryAdjustingYourSearch') }}</p>
        </div>
        @elseif (! $data->hasDates())
        {{-- Same plain prompt as the rate list's (components/availability-results-advanced). --}}
        <p class="my-3 py-1 @2xl:py-3 text-sm text-muted text-pretty">
            <svg class="inline-block size-4 align-[-0.2em] me-1" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M8 2v3M16 2v3M3 9h18M5 5h14a2 2 0 0 1 2 2v12a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V7a2 2 0 0 1 2-2Z"/></svg>
            {{ $selectDatesPrompt }}
        </p>
        @endif
    @endif
    @if ($errors->has('availability') && $data->hasDates())
    <div role="alert" class="rounded-lg border border-error/30 bg-error/5 p-5 mt-4">
        <p class="font-medium text-error mb-1">{{ trans('statamic-resrv::frontend.searchError') }}</p>
        <p class="text-sm text-muted">{{ $errors->first('availability') }}</p>
    </div>
    @endif
    @if ($errors->has('cutoff') && $data->hasDates())
    <div role="alert" class="rounded-lg border border-error/30 bg-error/5 p-5 mt-4">
        <p class="font-medium text-error mb-1">{{ trans('statamic-resrv::frontend.youAreTooLate') }}</p>
        <p class="text-sm text-muted">{{ $errors->first('cutoff') }}</p>
    </div>
    @endif
    </div>
    <div role="status" class="sr-only"><span wire:loading.delay.long>Updating availability…</span></div>
    <div class="absolute inset-0 bg-white/60" wire:loading.delay.long aria-hidden="true">
        <span class="flex items-center justify-center w-full h-full">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="animate-spin size-5 text-terracotta-dark" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0 3.181 3.183a8.25 8.25 0 0 0 13.803-3.7M4.031 9.865a8.25 8.25 0 0 1 13.803-3.7l3.181 3.182m0-4.991v4.99" />
            </svg>
        </span>
    </div>
</div>
