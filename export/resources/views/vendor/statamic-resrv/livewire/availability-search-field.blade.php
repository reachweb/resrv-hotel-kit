{{-- Field-only variant of the search, via view="availability-search-field": the hero
     card's date field with no card and no button, for a card that adds its own
     controls around it (the home Treatment tab: Date → Treatment → Find a time). That
     card reads the chosen date from this component and opens the target page with
     Resrv's ?date= deep link. Same sub-component and wiring as the default view —
     markup/classes only. --}}
<div class="h-full">
    <x-resrv::availability-dates
        :$calendar
        :$calendarRules
        :errors="$errors"
        variant="card"
        class="h-full"
    />
</div>
