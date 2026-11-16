@props(['calendar', 'disabledDays' => false, 'calendarRules' => [], 'errors', 'variant' => 'card'])

{{-- Restyled to the design's search-widget date fields. Markup/classes only — the
     datepicker Alpine component, calendar config and all Livewire wiring are untouched.
     The real (readonly) calendar input is an invisible overlay across the field so the
     bundled popup calendar keeps its anchor; the visible text mirrors `dates` reactively.
     Variants: card (hero widget) · compact (sticky bar) · panel (booking sidebar).

     Kit hardening (not in stock — keep through 3-way merges on upgrade):
     · `datepicker($wire, …)`: each instance binds to its OWN search component. Stock
       registers Alpine.data('datepicker') from @script with that script's `$wire`, and
       the last search on the page wins, so with two searches (the home hero's Stay and
       Table) the Stay calendar showed La Marea's prices and wrote its dates to the Table
       search, whose Search then failed with "The dates field is required".
     · Keyboard: focus stays on the field (combobox pattern) and the keys drive the open
       calendar; Tab closes it. The field shows a focus ring (the input is invisible).
     · Search with no dates (availability-button) asks for them here and opens the calendar.
     · Narrow fields (compact bar, booking panel at 320px): the value is a short range
       (`rangeLabel()`: "16–20 Nov", "28 Nov – 2 Dec") that truncates rather than running
       under the clear button, which has room reserved (compact) or sits on the label
       row (panel). A single date adds its year when it isn't this year ("5 Jan 2027").
     · An empty one-day field reads "Add date"; only the range fields say "Add dates".
       Chosen dates show without a leading zero ("3 Dec"), like the compact and panel
       fields, the day list and the checkout summary. --}}
@php
    $emptyValue = $calendar === 'range' ? 'Add dates' : 'Add date';
@endphp
<div class="{{ $attributes->get('class') }}">
    <div x-data="datepicker($wire, @js($disabledDays))" class="relative w-full h-full">

        <div wire:ignore class="h-full">
            <div
                x-data="calendar({
                    mode: '{{ $calendar === 'range' ? 'range' : 'single' }}',
                    display: 'popup',
                    format: 'DD MMM YYYY',
                    inputRef: 'dateInput',
                    months: 2,
                    mobileMonths: 12,
                    minDate: dayjs().add({{ config('resrv-config.minimum_days_before') }}, 'day').format('YYYY-MM-DD'),
                    @if ($calendar === 'range')
                    minRange: {{ config('resrv-config.minimum_reservation_period_in_days', 0) + 1 }},
                    maxRange: {{ config('resrv-config.maximum_reservation_period_in_days', 30) + 1 }},
                    @endif
                    rules: @json($calendarRules),
                    disabledDaysOfWeek: buildDisabledDaysOfWeek(),
                    dateMetadata: buildDateMetadata(),
                    value: getInitialValue(),
                })"
                x-ref="calendarInstance"
                @calendar:change="dateChanged($event.detail)"
                @calendar:open="onCalendarOpen()"
                class="h-full"
            >
                @if ($variant === 'compact')
                    {{-- Compact bar field: icon + label + combined range --}}
                    <div class="relative h-full hover:bg-shell transition-colors has-[input:focus-visible]:outline-2 has-[input:focus-visible]:-outline-offset-2 has-[input:focus-visible]:outline-terracotta-dark">
                        <div class="flex items-center gap-2.5 px-4 py-2.5 h-full text-left" x-bind:class="!isDatesEmpty && 'pe-10'">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" class="hidden sm:block size-4.5 text-sage shrink-0" aria-hidden="true"><path d="M8 2v3M16 2v3M3 9h18M5 5h14a2 2 0 0 1 2 2v12a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V7a2 2 0 0 1 2-2Z"/></svg>
                            <span class="min-w-0">
                                <span class="block text-xs font-semibold uppercase tracking-widest text-muted leading-tight">{{ $calendar === 'range' ? 'Dates' : 'Date' }}</span>
                                <span
                                    class="block text-sm font-medium truncate"
                                    x-bind:class="isDatesEmpty && 'text-muted'"
                                    x-text="isDatesEmpty ? @js($emptyValue) : rangeLabel()"
                                >{{ $emptyValue }}</span>
                            </span>
                        </div>
                @elseif ($variant === 'panel')
                    {{-- Booking-panel field: bordered box --}}
                    <div class="relative h-full border border-line-strong rounded-md p-3 text-left hover:border-ink transition-colors has-[input:focus-visible]:outline-2 has-[input:focus-visible]:-outline-offset-1 has-[input:focus-visible]:outline-terracotta-dark">
                        <span class="block text-xs font-semibold uppercase tracking-widest text-muted" x-bind:class="!isDatesEmpty && 'pe-6'">{{ $calendar === 'range' ? 'Dates' : 'Date' }}</span>
                        <span
                            class="block text-sm font-medium truncate mt-0.5"
                            x-bind:class="isDatesEmpty && 'text-muted'"
                            x-text="isDatesEmpty ? @js($emptyValue) : rangeLabel()"
                        >{{ $emptyValue }}</span>
                @else
                    {{-- Hero card fields: Check-in | Check-out (or a single Date field) --}}
                    <div class="relative h-full rounded-lg hover:bg-shell transition-colors has-[input:focus-visible]:outline-2 has-[input:focus-visible]:-outline-offset-2 has-[input:focus-visible]:outline-terracotta-dark">
                        @if ($calendar === 'range')
                            <div class="grid grid-cols-2 h-full">
                                {{-- The year shows from sm up: two 320px columns can't fit "20 Nov 2026" beside the clear button. --}}
                                <div class="sw-field min-h-15.5 min-w-0">
                                    <span class="text-xs font-semibold uppercase tracking-widest text-muted">Check-in</span>
                                    <span class="text-ui font-medium truncate" x-bind:class="!dates?.date_start && 'text-muted'">
                                        <span x-text="dates?.date_start ? dayjs(dates.date_start).format('D MMM') : 'Add dates'">Add dates</span><span class="hidden sm:inline" x-show="dates?.date_start" x-text="' ' + dayjs(dates?.date_start).format('YYYY')"></span>
                                    </span>
                                </div>
                                {{-- Divider inset like .sw-div (a border-l on the rounded field curved at its ends) --}}
                                <div class="sw-field min-h-15.5 min-w-0 relative before:absolute before:left-0 before:inset-y-2.5 before:w-px before:bg-line" x-bind:class="!isDatesEmpty && 'pe-9'">
                                    <span class="text-xs font-semibold uppercase tracking-widest text-muted">Check-out</span>
                                    <span class="text-ui font-medium truncate" x-bind:class="!dates?.date_end && 'text-muted'">
                                        <span x-text="dates?.date_end ? dayjs(dates.date_end).format('D MMM') : 'Add dates'">Add dates</span><span class="hidden sm:inline" x-show="dates?.date_end" x-text="' ' + dayjs(dates?.date_end).format('YYYY')"></span>
                                    </span>
                                </div>
                            </div>
                        @else
                            <div class="sw-field min-h-15.5 h-full">
                                <span class="text-xs font-semibold uppercase tracking-widest text-muted">Date</span>
                                <span
                                    class="text-ui font-medium whitespace-nowrap"
                                    x-bind:class="isDatesEmpty && 'text-muted'"
                                    x-text="isDatesEmpty ? @js($emptyValue) : dayjs(dates.date_start).format('D MMM YYYY')"
                                >{{ $emptyValue }}</span>
                            </div>
                        @endif
                @endif

                    {{-- The real calendar input — invisible click-target covering the field --}}
                    <input
                        name="datepicker"
                        type="text"
                        readonly
                        x-ref="dateInput"
                        placeholder="{{ trans_choice('statamic-resrv::frontend.selectDate', ($calendar === 'range') ? 2 : 1) }}"
                        aria-label="{{ trans_choice('statamic-resrv::frontend.selectDate', ($calendar === 'range') ? 2 : 1) }}"
                        x-on:keydown="calendarKeydown($event)"
                        x-on:availability-dates-required.window="$event.detail.id === $wire.$id && askForDates()"
                        x-bind:aria-activedescendant="isOpen && activeDescendantId ? activeDescendantId : null"
                        class="absolute inset-0 w-full h-full opacity-0 cursor-pointer"
                    />
                    <div x-show="! isDatesEmpty" x-cloak @class([
                        'absolute flex items-center z-10',
                        'top-1.5 end-1.5' => $variant === 'panel',
                        'inset-y-0 end-1.5' => $variant === 'card',
                        'inset-y-0 end-2' => $variant === 'compact',
                    ])>
                        <button
                            type="button"
                            x-on:click.stop="clearSelection()"
                            class="cursor-pointer p-1 rounded-full text-muted hover:text-ink hover:bg-sand transition-colors"
                            aria-label="Clear selection"
                        >
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" class="size-4.5" aria-hidden="true">
                                <path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M18 6l-12 12" /><path d="M6 6l12 12" />
                            </svg>
                        </button>
                    </div>
                    <div
                        x-data="{ loading: false }"
                        x-on:availability-search-updated.window="loading = true"
                        x-on:availability-results-updated.window="setTimeout(() => {loading = false}, 300)"
                    >
                        <span x-show="loading === true" class="absolute inset-0 flex items-center justify-center bg-white/60" aria-hidden="true">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="animate-spin size-5 text-teal" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0 3.181 3.183a8.25 8.25 0 0 0 13.803-3.7M4.031 9.865a8.25 8.25 0 0 1 13.803-3.7l3.181 3.182m0-4.991v4.99" />
                            </svg>
                        </span>
                    </div>
                </div>
            </div>
        </div>

        {{-- Server validation of chosen dates. One line per distinct message: the three
             keys often repeat each other, and stock rendered an empty line for each miss. --}}
        @php
            $dateErrors = array_values(array_unique(array_filter([
                $errors->first('data.dates'),
                $errors->first('data.dates.date_start'),
                $errors->first('data.dates.date_end'),
            ])));
        @endphp
        {{-- Both notes anchor to the field via $root: from here `$refs.dateInput` lives in
             the calendar's scope and doesn't resolve, so stock's anchor fell back to normal
             flow and stretched the card. `.fixed` keeps them out of the compact bar's
             overflow-hidden pill, while staying in place keeps them inside their tab panel
             (a teleported note would float over the next tab of the home card). --}}
        @if ($dateErrors)
        <div role="alert" x-show="!isDatesEmpty" x-anchor.fixed.bottom-start.offset.10="$root" class="z-40 max-w-sm bg-white border border-line rounded-md px-4 py-2 shadow-card text-error text-sm space-y-1">
            @foreach ($dateErrors as $message)
                <span class="block">{{ $message }}</span>
            @endforeach
        </div>
        @endif

        {{-- Search pressed with no dates: say what's missing (the calendar opens too). On
             the hero card's stacked phone layout a floating note would cover Guests, so
             there it sits in the flow under the field instead. --}}
        @php($datesPrompt = $calendar === 'range' ? 'Choose your check-in and check-out dates to search.' : 'Choose a date to search.')
        <p
            role="alert"
            x-show="datesRequired && isDatesEmpty"
            x-cloak
            x-anchor.fixed.bottom-start.offset.10="$root"
            class="{{ $variant === 'card' ? 'hidden md:block' : '' }} z-40 max-w-sm bg-white border border-line rounded-md px-4 py-2 shadow-card text-error text-sm"
        >{{ $datesPrompt }}</p>
        @if ($variant === 'card')
            <p role="alert" x-show="datesRequired && isDatesEmpty" x-cloak class="md:hidden px-4.5 pt-1 pb-2 text-error text-sm">{{ $datesPrompt }}</p>
        @endif
    </div>
</div>

@script
<script>
// `$wire` and `disabledDays` are passed in by each instance's x-data (see the note at
// the top): the factory is shared by every search on the page, so it must not read the
// script-scoped `$wire` or render per-instance Blade values. The parameter keeps the
// stock name so the body below stays the stock code.
Alpine.data('datepicker', ($wire, disabledDays = false) => ({
    // Livewire & Config Properties
    mode: $wire.calendar,
    dates: $wire.data.dates,
    rates: $wire.rates,
    rateSelected: $wire.entangle('data.rate'),
    minPeriod: {{ config('resrv-config.minimum_reservation_period_in_days', 0) }},
    maxPeriod: {{ config('resrv-config.maximum_reservation_period_in_days', 30) }},
    disabledDays: disabledDays,
    showAvailabilityOnCalendar: $wire.showAvailabilityOnCalendar,
    availabilityCalendar: [],
    datesRequired: false,

    get isDatesEmpty() {
        return !this.dates || Object.keys(this.dates).length === 0 || (!this.dates.date_start && !this.dates.date_end);
    },

    init() {
        this.$watch('rateSelected', async () => {
            if (!this.showAvailabilityOnCalendar) return;
            // Skip if user has never opened the calendar yet — onCalendarOpen
            // will fetch with the latest rateSelected on first open.
            if (!this.availabilityCalendar || Object.keys(this.availabilityCalendar).length === 0) return;
            this.availabilityCalendar = await this.fetchAvailability();
            const cal = Alpine.$data(this.$refs.calendarInstance);
            cal?.updateDateMetadata?.(this.buildDateMetadata());
        });
    },

    async onCalendarOpen() {
        if (!this.showAvailabilityOnCalendar) return;
        if (this.availabilityCalendar && Object.keys(this.availabilityCalendar).length > 0) return;
        this.availabilityCalendar = await this.fetchAvailability();
        const cal = Alpine.$data(this.$refs.calendarInstance);
        cal?.updateDateMetadata?.(this.buildDateMetadata());
    },

    async fetchAvailability() {
        if (this.rates !== false && this.rateSelected === null) {
            return [];
        }
        return await $wire.availabilityCalendar();
    },

    // The compact and panel fields' value: "16–20 Nov" within a month, "28 Nov – 2 Dec"
    // across months, "16 Nov" for a single date. Short enough for a 320px phone. A single
    // date in another year names it ("5 Jan 2027"); a one-day field has the room.
    rangeLabel() {
        if (this.isDatesEmpty) return '';
        const start = dayjs(this.dates.date_start);
        if (this.mode !== 'range' || !this.dates.date_end) {
            return start.format(start.isSame(dayjs(), 'year') ? 'D MMM' : 'D MMM YYYY');
        }
        const end = dayjs(this.dates.date_end);
        return start.isSame(end, 'month')
            ? start.format('D') + '–' + end.format('D MMM')
            : start.format('D MMM') + ' – ' + end.format('D MMM');
    },

    getInitialValue() {
        if (this.isDatesEmpty) return '';
        let start = this.toDateOnly(this.dates.date_start);
        if (this.mode === 'range' && this.dates.date_end) {
            return start + ' - ' + this.toDateOnly(this.dates.date_end);
        }
        return start;
    },

    // The calendar emits date-only 'YYYY-MM-DD' strings and the server reads
    // data.dates as wall-clock dates in the app timezone. Always normalize to
    // date-only before sending over the wire: a no-arg dayjs().format() appends
    // the browser's UTC offset, which shifts the selected day back for visitors
    // ahead of the server timezone (e.g. +03:00 against a UTC server).
    toDateOnly(date) {
        return dayjs(date).format('YYYY-MM-DD');
    },

    buildDateMetadata() {
        if (!this.availabilityCalendar || Object.keys(this.availabilityCalendar).length === 0) return {};
        const cal = this.availabilityCalendar;
        const symbol = '{{ config('resrv-config.currency_symbol') }}';
        return (date) => {
            const info = cal[date.toISO()];
            if (info && info.available > 0) {
                return { label: symbol + Math.round(info.price), availability: 'available' };
            }
            return { availability: 'unavailable' };
        };
    },

    buildDisabledDaysOfWeek() {
        if (!this.disabledDays) return [];
        const dayMap = { 'Sunday': 0, 'Monday': 1, 'Tuesday': 2, 'Wednesday': 3, 'Thursday': 4, 'Friday': 5, 'Saturday': 6 };
        return this.disabledDays.map(d => dayMap[d]).filter(d => d !== undefined);
    },

    dateChanged(detail) {
        const isoDates = detail.dates;

        if (!isoDates || isoDates.length === 0) {
            // Empty selection — short-circuit if already empty to avoid double-firing
            // when clearSelection() triggers calendar:change.
            if (this.isDatesEmpty) return;
            this.dates = {};
            $wire.clearDates();
            $dispatch('availability-search-cleared');
            return;
        }

        const newDates = this.mode === 'range'
            ? { date_start: this.toDateOnly(isoDates[0]), date_end: this.toDateOnly(isoDates[1]) }
            : { date_start: this.toDateOnly(isoDates[0]), date_end: this.toDateOnly(dayjs(isoDates[0]).add(1, 'day')) };

        this.dates = newDates;
        this.datesRequired = false;
        $wire.set('data.dates', newDates);
    },

    clearSelection() {
        // Calendar's clear() emits @calendar:change with empty dates →
        // dateChanged() handles the Livewire side. Single source of truth.
        const cal = Alpine.$data(this.$refs.calendarInstance);
        cal?.clear?.();
        cal?.close?.();
    },

    // Search was pressed with no dates (availability-button stops the request): show
    // the prompt and move focus to the field, which opens the calendar. Called from the
    // input, where `$refs` reaches into the calendar's scope.
    askForDates() {
        this.datesRequired = true;
        this.$refs.dateInput.focus();
        const cal = Alpine.$data(this.$refs.calendarInstance);
        if (cal && !cal.isOpen) cal.open();
    },

    // The calendar's own keyboard handling lives on its popup grid, which never gets
    // focus. Forward the navigation keys from the field instead: arrows / Page / Home /
    // End move the active day, Enter or Space picks it, Tab closes the calendar and moves
    // on. Closed, Enter / Space / ArrowDown open it (Alt+ArrowDown and Escape are stock).
    calendarKeydown(event) {
        const cal = Alpine.$data(this.$refs.calendarInstance);
        if (!cal) return;

        if (!cal.isOpen) {
            if (['Enter', ' ', 'ArrowDown'].includes(event.key) && !event.altKey) {
                event.preventDefault();
                cal.open();
            }
            return;
        }

        if (event.key === 'Tab') {
            cal.close();
            return;
        }

        if (['ArrowLeft', 'ArrowRight', 'ArrowUp', 'ArrowDown', 'PageUp', 'PageDown', 'Home', 'End', 'Enter', ' '].includes(event.key)) {
            cal.handleKeydown(event);
        }
    },
}));
</script>
@endscript
