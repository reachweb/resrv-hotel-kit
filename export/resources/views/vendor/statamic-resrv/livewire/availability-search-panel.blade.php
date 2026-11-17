{{-- Booking-panel variant of the search (room-detail.html): two bordered boxes in a
     grid (Dates | Guests), used in the room-detail sticky sidebar via
     view="availability-search-panel". Same sub-components/wiring — markup/classes only.
     Field boxes take Border Strong: they identify controls (WCAG 1.4.11), so they read
     as inputs rather than as cards inside the white booking panel.
     Optional closed weekdays: a venue's "Closed days" arrive on the tag as
     closed-days="Monday" (comma-separated English day names; La Marea's band). It isn't
     a property of the search, so Livewire forwards it to this view as an HTML attribute,
     and the calendar disables those weekdays through availability-dates' `disabledDays`,
     as availability-search-spa does. Without it (rooms, treatments) nothing changes. --}}
@php
    $closedDays = array_values(array_filter(array_map('trim', explode(',', (string) $attributes->get('closed-days')))));
@endphp
<div class="relative grid grid-cols-2 gap-2 text-ink">

    <x-resrv::availability-dates
        :$calendar
        :$calendarRules
        :disabledDays="$closedDays ?: false"
        :errors="$errors"
        variant="panel"
        class="{{ $calendar === 'range' ? '' : 'col-span-2' }}"
    />

    @if ($calendar === 'range')
    <x-resrv::availability-guests variant="panel" />
    @endif

    @if ($rates)
    <x-resrv::availability-rates
        wire:model.live="data.rate"
        :entryRates="$this->entryRates"
        :errors="$errors"
        class="col-span-2"
    />
    @endif

    @if ($enableQuantity)
    <x-resrv::availability-quantity
        :maxQuantity="$this->maxQuantity"
        :errors="$errors"
        class="col-span-2 border border-line-strong rounded-md p-3"
    />
    @endif

    @if ($live === false)
    <x-resrv::availability-button class="col-span-2" />
    @endif
</div>
