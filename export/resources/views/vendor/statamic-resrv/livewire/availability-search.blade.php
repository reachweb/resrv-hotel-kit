{{-- Default search view, restyled to the design's hero search card (home.html):
     white raised card · Check-in/Check-out · Guests · Search. Sub-component wiring
     and the Livewire structure are stock — markup/classes only.
     Page-specific variants live in availability-search-compact / -panel (view="…"). --}}
<div class="relative">
    <div class="bg-white rounded-xl shadow-raised p-2.5 text-ink">
        <div class="flex flex-col md:flex-row md:items-stretch gap-1.5 md:gap-0">

            <x-resrv::availability-dates
                :$calendar
                :$calendarRules
                :errors="$errors"
                variant="card"
                class="flex-1 {{ $calendar === 'range' ? 'md:flex-[2.4]' : 'md:flex-[1.6]' }}"
            />

            @if ($calendar === 'range')
            <div class="sw-div hidden md:block"></div>
            <x-resrv::availability-guests
                variant="card"
                class="flex-1 md:flex-[1.1]"
            />
            @endif

            @if ($rates)
            <div class="sw-div hidden md:block"></div>
            <x-resrv::availability-rates
                wire:model.live="data.rate"
                :entryRates="$this->entryRates"
                :errors="$errors"
                class="flex flex-col justify-center px-2 flex-1 md:flex-[1.1]"
            />
            @endif

            @if ($enableQuantity)
            <div class="sw-div hidden md:block"></div>
            <x-resrv::availability-quantity
                :maxQuantity="$this->maxQuantity"
                :errors="$errors"
                class="flex flex-col justify-center px-[18px] py-2 flex-1 min-h-[62px]"
            />
            @endif

            @if ($live === false)
            <div class="p-1.5 md:pl-2.5 flex md:items-stretch">
                <x-resrv::availability-button class="w-full" />
            </div>
            @endif
        </div>
    </div>
</div>
