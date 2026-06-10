@props(['calendar', 'disabledDays' => false, 'calendarRules' => [], 'errors', 'variant' => 'card'])

{{-- Restyled to the design's search-widget date fields. Markup/classes only — the
     datepicker Alpine component, calendar config and all Livewire wiring are untouched.
     The real (readonly) calendar input is an invisible overlay across the field so the
     bundled popup calendar keeps its anchor; the visible text mirrors `dates` reactively.
     Variants: card (hero widget) · compact (sticky bar) · panel (booking sidebar). --}}
<div class="{{ $attributes->get('class') }}">
    <div x-data="datepicker" class="relative w-full h-full">

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
                    <div class="relative h-full hover:bg-shell transition-colors">
                        <div class="flex items-center gap-2.5 px-4 py-2.5 h-full text-left">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" class="w-[18px] h-[18px] text-sage shrink-0"><path d="M8 2v3M16 2v3M3 9h18M5 5h14a2 2 0 0 1 2 2v12a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V7a2 2 0 0 1 2-2Z"/></svg>
                            <span>
                                <span class="block text-xs font-semibold uppercase tracking-[0.12em] text-muted leading-tight">{{ $calendar === 'range' ? 'Dates' : 'Date' }}</span>
                                <span
                                    class="block text-sm font-medium whitespace-nowrap"
                                    x-bind:class="isDatesEmpty && 'text-muted'"
                                    x-text="isDatesEmpty ? 'Add dates' : (dayjs(dates.date_start).format('DD MMM') + (mode === 'range' && dates.date_end ? ' – ' + dayjs(dates.date_end).format('DD MMM') : ''))"
                                >Add dates</span>
                            </span>
                        </div>
                @elseif ($variant === 'panel')
                    {{-- Booking-panel field: bordered box --}}
                    <div class="relative h-full border border-line rounded-md p-3 text-left hover:border-ink transition-colors">
                        <span class="block text-xs font-semibold uppercase tracking-[0.12em] text-muted">{{ $calendar === 'range' ? 'Dates' : 'Date' }}</span>
                        <span
                            class="block text-sm font-medium whitespace-nowrap mt-0.5"
                            x-bind:class="isDatesEmpty && 'text-muted'"
                            x-text="isDatesEmpty ? 'Add dates' : (dayjs(dates.date_start).format('DD MMM') + (mode === 'range' && dates.date_end ? ' – ' + dayjs(dates.date_end).format('DD MMM') : ''))"
                        >Add dates</span>
                @else
                    {{-- Hero card fields: Check-in | Check-out (or a single Date field) --}}
                    <div class="relative h-full rounded-lg hover:bg-shell transition-colors">
                        @if ($calendar === 'range')
                            <div class="grid grid-cols-2 h-full">
                                <div class="sw-field min-h-[62px]">
                                    <span class="text-xs font-semibold uppercase tracking-[0.12em] text-muted">Check-in</span>
                                    <span
                                        class="text-[15px] font-medium whitespace-nowrap"
                                        x-bind:class="!dates?.date_start && 'text-muted'"
                                        x-text="dates?.date_start ? dayjs(dates.date_start).format('DD MMM YYYY') : 'Add dates'"
                                    >Add dates</span>
                                </div>
                                <div class="sw-field min-h-[62px] border-l border-line">
                                    <span class="text-xs font-semibold uppercase tracking-[0.12em] text-muted">Check-out</span>
                                    <span
                                        class="text-[15px] font-medium whitespace-nowrap"
                                        x-bind:class="!dates?.date_end && 'text-muted'"
                                        x-text="dates?.date_end ? dayjs(dates.date_end).format('DD MMM YYYY') : 'Add dates'"
                                    >Add dates</span>
                                </div>
                            </div>
                        @else
                            <div class="sw-field min-h-[62px] h-full">
                                <span class="text-xs font-semibold uppercase tracking-[0.12em] text-muted">Date</span>
                                <span
                                    class="text-[15px] font-medium whitespace-nowrap"
                                    x-bind:class="isDatesEmpty && 'text-muted'"
                                    x-text="isDatesEmpty ? 'Add dates' : dayjs(dates.date_start).format('DD MMM YYYY')"
                                >Add dates</span>
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
                        class="absolute inset-0 w-full h-full opacity-0 cursor-pointer"
                    />
                    <div x-show="! isDatesEmpty" x-cloak class="absolute {{ $variant === 'card' ? 'top-1.5 end-1.5' : 'inset-y-0 end-2 items-center' }} flex z-10">
                        <button
                            type="button"
                            x-on:click.stop="clearSelection()"
                            class="cursor-pointer p-1 rounded-full text-muted hover:text-ink hover:bg-sand transition-colors"
                            aria-label="Clear selection"
                        >
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" class="w-4 h-4">
                                <path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M18 6l-12 12" /><path d="M6 6l12 12" />
                            </svg>
                        </button>
                    </div>
                    <div
                        x-data="{ loading: false }"
                        x-on:availability-search-updated.window="loading = true"
                        x-on:availability-results-updated.window="setTimeout(() => {loading = false}, 300)"
                    >
                        <span x-show="loading === true" class="absolute left-0 right-0 top-0 flex items-center justify-center w-full h-full bg-white/60">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="animate-spin w-5 h-5 text-teal">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0 3.181 3.183a8.25 8.25 0 0 0 13.803-3.7M4.031 9.865a8.25 8.25 0 0 1 13.803-3.7l3.181 3.182m0-4.991v4.99" />
                            </svg>
                        </span>
                    </div>
                </div>
            </div>
        </div>

        @if ($errors->has('data.dates') || $errors->has('data.dates.date_start') || $errors->has('data.date_end'))
        <div class="bg-white border border-line rounded-md px-4 py-2 shadow-card text-error text-sm space-y-1 z-10" x-anchor.offset.10="$refs.dateInput" x-show="!isDatesEmpty">
            <span class="block">{{ $errors->first('data.dates') }}</span>
            <span class="block">{{ $errors->first('data.dates.date_start') }}</span>
            <span class="block">{{ $errors->first('data.dates.date_end') }}</span>
        </div>
        @endif
    </div>
</div>

<style>
    .rc-day__label, .rc-day__dot {
        animation: rc-fade-in 0.3s ease-out;
    }
    @keyframes rc-fade-in {
        from { opacity: 0; transform: translateY(2px); }
        to { opacity: 1; transform: translateY(0); }
    }
    .rc-popup-overlay {
        z-index: 300 !important;
    }
</style>

@script
<script>
Alpine.data('datepicker', () => ({
    // Livewire & Config Properties
    mode: $wire.calendar,
    dates: $wire.data.dates,
    rates: $wire.rates,
    rateSelected: $wire.entangle('data.rate'),
    minPeriod: {{ config('resrv-config.minimum_reservation_period_in_days', 0) }},
    maxPeriod: {{ config('resrv-config.maximum_reservation_period_in_days', 30) }},
    disabledDays: @json($disabledDays),
    showAvailabilityOnCalendar: $wire.showAvailabilityOnCalendar,
    availabilityCalendar: [],

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

    getInitialValue() {
        if (this.isDatesEmpty) return '';
        let start = dayjs(this.dates.date_start).format('YYYY-MM-DD');
        if (this.mode === 'range' && this.dates.date_end) {
            return start + ' - ' + dayjs(this.dates.date_end).format('YYYY-MM-DD');
        }
        return start;
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

        const dateStart = dayjs(isoDates[0]);
        const newDates = this.mode === 'range'
            ? { date_start: dateStart.format(), date_end: dayjs(isoDates[1]).format() }
            : { date_start: dateStart.format(), date_end: dateStart.add(1, 'day').format() };

        this.dates = newDates;
        $wire.set('data.dates', newDates);
    },

    clearSelection() {
        // Calendar's clear() emits @calendar:change with empty dates →
        // dateChanged() handles the Livewire side. Single source of truth.
        const cal = Alpine.$data(this.$refs.calendarInstance);
        cal?.clear?.();
        cal?.close?.();
    },
}));
</script>
@endscript
