{{-- Compact sticky-bar variant of the search (rooms.html): bordered white pill with
     icon fields, used inside the rooms-index sticky bar via view="availability-search-compact".
     Same sub-components/wiring as the default view — markup/classes only.
     The pill's edge is Border Strong (it identifies the control: WCAG 1.4.11), and the
     fields are min-w-0 so their values truncate instead of running under the clear
     button at 320px. --}}
<div class="relative flex flex-col lg:flex-row lg:items-center gap-2">
    <div class="flex flex-1 items-stretch rounded-md bg-white border border-line-strong overflow-hidden text-ink">

        <x-resrv::availability-dates
            :$calendar
            :$calendarRules
            :errors="$errors"
            variant="compact"
            class="flex-1 min-w-0"
        />

        @if ($calendar === 'range')
        <div class="w-px bg-line my-2 shrink-0"></div>
        <x-resrv::availability-guests
            variant="compact"
            class="flex-1 min-w-0"
        />
        @endif

        @if ($rates)
        <div class="w-px bg-line my-2 shrink-0"></div>
        <x-resrv::availability-rates
            wire:model.live="data.rate"
            :entryRates="$this->entryRates"
            :errors="$errors"
            class="flex flex-col justify-center px-2"
        />
        @endif

        @if ($enableQuantity)
        <div class="w-px bg-line my-2 shrink-0"></div>
        <x-resrv::availability-quantity
            :maxQuantity="$this->maxQuantity"
            :errors="$errors"
            class="flex flex-col justify-center px-4 py-2 min-w-44"
        />
        @endif
    </div>

    @if ($live === false)
    <x-resrv::availability-button class="shrink-0" label="Update search" />
    @endif
</div>
