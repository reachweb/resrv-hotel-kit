@use(Carbon\Carbon)

{{-- The spa menu as Resrv's availability-collection, via
     view="availability-collection-treatments" (partials/blocks/treatments_grid).
     Markup/classes only — every wire: directive and the $data / $this->rows structure
     are stock. The card is components/partials/treatment-card:
       • before a day is picked → every treatment as a catalog card with its "from"
         price, so the menu is never an empty "pick a date" prompt;
       • after → the treatments free that day, each with its durations' live prices
         and appointment times. The ones that aren't free (showUnavailable="true") are
         named under the grid, so a missing treatment never looks like a missing menu
         item. With none free (a closed day, or fully booked) one note says so.
     While a search loads, the grid wrapper is aria-busy and one sr-only status (outside
     it, so it isn't held back) says "Updating availability…". Errors are role="alert". --}}
<div class="relative">
    <div wire:loading.delay.long.attr="aria-busy">
    @if (! $data->hasDates())
        <p class="text-ui text-ink/80 mb-6">Pick a day to see which treatments are free, and at what times.</p>
        <div class="grid gap-5 md:grid-cols-2 lg:grid-cols-3" role="list">
            @foreach ($this->resolvedEntries as $entry)
                @include('statamic-resrv::livewire.components.partials.treatment-card', ['entry' => $entry, 'row' => null])
            @endforeach
        </div>
    @else
        @php($day = Carbon::parse($data->dates['date_start'])->format('l j F'))
        @php($free = $this->rows->where('available', true))
        @php($taken = $this->rows->where('available', false))
        @if ($free->isEmpty())
            @unless ($errors->has('availability'))
                <div class="rounded-lg border border-line bg-white p-10 text-center">
                    <p class="t-h3 mb-2">Nothing free on {{ $day }}</p>
                    <p class="text-base leading-relaxed text-muted">The spa may be closed or fully booked that day. Try another day.</p>
                </div>
            @endunless
        @else
            <p class="text-ui text-ink/80 mb-6">{{ trans_choice(':count treatment|:count treatments', $free->count()) }} free on <span class="font-semibold text-ink">{{ $day }}</span></p>
            <div class="grid gap-5 md:grid-cols-2 lg:grid-cols-3" role="list">
                @foreach ($free as $row)
                    @include('statamic-resrv::livewire.components.partials.treatment-card', ['entry' => $row['entry'], 'row' => $row])
                @endforeach
            </div>
            @if ($taken->isNotEmpty())
                <p class="mt-6 text-sm text-muted">
                    Not free that day:
                    @foreach ($taken as $row)
                        <a href="{{ $row['entry']->url() }}" class="font-medium text-ink underline decoration-line-strong underline-offset-4 hover:text-terracotta-dark hover:decoration-current transition-colors">{{ $row['entry']->get('title') }}</a>{{ $loop->last ? '.' : ',' }}
                    @endforeach
                    Open one to see the days it's free.
                </p>
            @endif
        @endif
    @endif

    @if ($errors->has('availability') && $data->hasDates())
        <div role="alert" class="rounded-lg border border-error/30 bg-error/5 p-4 mt-4">
            <p class="text-sm font-medium text-error">{{ trans('statamic-resrv::frontend.searchError') }}</p>
            <p class="text-sm text-muted">{{ $errors->first('availability') }}</p>
        </div>
    @endif
    </div>

    <div role="status" class="sr-only"><span wire:loading.delay.long>Updating availability…</span></div>
    <div class="absolute inset-0 bg-sand/50" wire:loading.delay.long aria-hidden="true">
        <span class="flex h-full w-full items-center justify-center">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6 animate-spin text-teal" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0 3.181 3.183a8.25 8.25 0 0 0 13.803-3.7M4.031 9.865a8.25 8.25 0 0 1 13.803-3.7l3.181 3.182m0-4.991v4.99" />
            </svg>
        </span>
    </div>
</div>
