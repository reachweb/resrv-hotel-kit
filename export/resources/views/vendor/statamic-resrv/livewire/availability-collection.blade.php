{{-- Restyled to the Resrv Hotel card grid. Markup/classes only — all wire: directives
     and the $data / $this->rows / select() structure are preserved. The card itself is
     components/partials/room-card so both states share one shell:
       • before a search → every room as a catalog card with its "from" price, so the
         listing is never an empty "please pick dates" prompt;
       • after a search → live availability (rates / Book / sold-out). --}}
<div class="relative">
    @if (! $data->hasDates())
        <div class="grid gap-6 md:grid-cols-2 lg:grid-cols-3" role="list">
            @foreach ($this->resolvedEntries as $entry)
                @include('statamic-resrv::livewire.components.partials.room-card', ['entry' => $entry, 'row' => null, 'party' => 0])
            @endforeach
        </div>
    @else
        {{-- Party size (infants sleep in a cot, so they don't count toward beds). Used only
             to flag rooms whose max_occupancy is below it — Resrv itself ignores occupancy. --}}
        @php($party = (int) ($data->customer['adults'] ?? 0) + (int) ($data->customer['children'] ?? 0))
        @if ($this->rows->isEmpty())
            <div class="rounded-lg border border-line bg-white p-10 text-center">
                <p class="t-h3 mb-2">{{ trans('statamic-resrv::frontend.noAvailability') }}</p>
                <p class="text-base leading-relaxed text-muted">{{ trans('statamic-resrv::frontend.tryAdjustingYourSearch') }}</p>
            </div>
        @else
            @php($undersizedRooms = $party > 0 && $this->rows->contains(fn ($r) => (int) $r['entry']->get('max_occupancy') > 0 && (int) $r['entry']->get('max_occupancy') < $party))
            @if ($undersizedRooms)
                <div class="flex items-start gap-2.5 rounded-lg border border-line bg-sand/60 p-4 mb-6 text-sm text-ink/80" role="status">
                    <svg class="w-4 h-4 text-warning shrink-0 mt-0.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="9"/><path d="M12 8h.01M11 12h1v4h1"/></svg>
                    <span>Showing every room for your dates. Rooms tagged <span class="font-medium text-warning">“Sleeps up to …”</span> are smaller than your party of {{ $party }} — still bookable, but a larger room may suit you better.</span>
                </div>
            @endif
            <div class="grid gap-6 md:grid-cols-2 lg:grid-cols-3" role="list">
                @foreach ($this->rows as $row)
                    @include('statamic-resrv::livewire.components.partials.room-card', ['entry' => $row['entry'], 'row' => $row, 'party' => $party])
                @endforeach
            </div>
        @endif

        @if ($paginate)
            <div class="mt-8">{{ $this->resolvedEntries->links() }}</div>
        @endif
    @endif

    @if ($errors->has('availability') && $data->hasDates())
        <div class="rounded-lg border border-error/30 bg-error/5 p-4 mt-4">
            <p class="text-sm font-medium text-error">{{ trans('statamic-resrv::frontend.searchError') }}</p>
            <p class="text-sm text-muted">{{ $errors->first('availability') }}</p>
        </div>
    @endif

    <div class="absolute inset-0 h-full w-full bg-shell/50" wire:loading.delay.long>
        <span class="flex h-full w-full items-center justify-center">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="h-6 w-6 animate-spin text-teal">
                <path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0 3.181 3.183a8.25 8.25 0 0 0 13.803-3.7M4.031 9.865a8.25 8.25 0 0 1 13.803-3.7l3.181 3.182m0-4.991v4.99" />
            </svg>
        </span>
    </div>
</div>
