{{-- The spa menu's one-day search (partials/blocks/treatments_grid), via
     view="availability-search-spa". The compact bar's pill (availability-search-compact)
     with only its date field, plus the spa's closed weekdays switched off in the calendar
     through availability-dates' documented `disabledDays`.

     The weekdays come from the treatments_grid set's "Closed days" field, passed on the
     tag as closed-days="Tuesday" (comma-separated English day names). It isn't a
     property of the search, so Livewire 4 forwards it to this view as an HTML attribute
     ($attributes) and keeps it for later requests. Without it nothing is disabled.
     The calendar only blocks the pick: a ?date= link or a session date on a closed day
     still loads, and the treatments view says the spa is closed that day. --}}
@php
    $closedDays = array_values(array_filter(array_map('trim', explode(',', (string) $attributes->get('closed-days')))));
@endphp
<div class="relative">
    <div class="flex items-stretch rounded-md bg-white border border-line-strong overflow-hidden text-ink">
        <x-resrv::availability-dates
            :$calendar
            :$calendarRules
            :disabledDays="$closedDays ?: false"
            :errors="$errors"
            variant="compact"
            class="flex-1 min-w-0"
        />
    </div>
</div>
