{{-- Compact sticky-bar variant of the search (rooms.html): bordered white pill with
     icon fields, used inside the rooms-index sticky bar via view="availability-search-compact".
     Same sub-components/wiring as the default view — markup/classes only. --}}
<div class="relative flex flex-col lg:flex-row lg:items-center gap-2">
    <div class="flex flex-1 items-stretch rounded-md bg-white border border-line overflow-hidden text-ink">

        <x-resrv::availability-dates
            :$calendar
            :$calendarRules
            :errors="$errors"
            variant="compact"
            class="flex-1"
        />

        @if ($calendar === 'range')
        <div class="w-px bg-line my-2 shrink-0"></div>
        <x-resrv::availability-guests
            variant="compact"
            class="flex-1"
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
