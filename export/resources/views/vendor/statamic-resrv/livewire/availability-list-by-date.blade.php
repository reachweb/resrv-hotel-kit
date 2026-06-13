@use(Carbon\Carbon)

<div class="relative">
    @if ($availableDates->isNotEmpty())
        <div class="divide-y divide-line">
            <div class="pb-4">
                <h3 class="text-xl font-semibold">{{ trans('statamic-resrv::frontend.availableDates') }}</h3>
                <p class="text-sm text-muted mt-0.5">Pick a day to load it into the panel above.</p>
            </div>

            {{-- Display cap: the engine returns every future date with availability —
                 show the next 7; clicking a later day re-anchors the window. --}}
            @foreach ($availableDates->take(7) as $date => $rateEntries)
                @php($isSelected = $data->hasDates() && Carbon::parse($date)->isSameDay(Carbon::parse($data->dates['date_start'])))
                @php($fromPrice = collect($rateEntries)->min(fn ($info) => (float) $info['price']))
                {{-- x-on:click (not @click — Blade would parse @ as a directive) smoothly
                     brings the search/results panel back into view; block:'nearest' makes it
                     a no-op when the panel is already visible, so it only scrolls when it
                     makes sense (e.g. you're scrolled down at the list on mobile). The target
                     sits above the dynamic results/list, so its position is morph-stable. --}}
                <button type="button"
                        wire:click="selectDate('{{ $date }}')"
                        wire:key="day-{{ $date }}"
                        x-on:click="document.querySelector('[data-resrv-booking-top]')?.scrollIntoView({ behavior: 'smooth', block: 'nearest' })"
                        @if ($isSelected) aria-current="date" @endif
                        class="group w-full flex items-center justify-between gap-4 py-4 text-left cursor-pointer focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-terracotta">
                    <span class="flex items-baseline gap-3">
                        <span class="block">
                            <span class="block text-xs font-semibold uppercase tracking-[0.12em] text-muted">
                                {{ Carbon::parse($date)->format('D') }}
                            </span>
                            <span class="block text-lg font-medium {{ $isSelected ? 'text-terracotta-dark' : 'group-hover:text-terracotta-dark transition-colors' }}">
                                {{ Carbon::parse($date)->format('j M Y') }}
                            </span>
                        </span>
                        <span class="text-sm text-muted">
                            {{ count($rateEntries) }} {{ trans_choice('statamic-resrv::frontend.optionsAvailable', count($rateEntries)) }}
                        </span>
                    </span>
                    <span class="flex items-center gap-2.5 shrink-0 text-muted">
                        <span class="text-sm">{{ trans('statamic-resrv::frontend.from') }} {{ config('resrv-config.currency_symbol') }}{{ $fromPrice }}</span>
                        <svg class="w-4 h-4 group-hover:translate-x-0.5 transition-transform" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="m9 18 6-6-6-6"/></svg>
                    </span>
                </button>
            @endforeach
            @if ($availableDates->count() > 7)
            <p class="text-sm text-muted pt-4">Showing the next 7 days with availability &mdash; pick a later date above to see more.</p>
            @endif
        </div>
    @elseif ($data->hasDates())
        <div class="py-2">
            <p class="font-medium">{{ trans('statamic-resrv::frontend.noAvailableDates') }}</p>
            <p class="text-sm text-muted mt-0.5">{{ trans('statamic-resrv::frontend.tryAdjustingYourSearch') }}</p>
        </div>
    @else
        <div class="py-2">
            <p class="font-medium">{{ trans('statamic-resrv::frontend.pleaseSelectStartDate') }}</p>
        </div>
    @endif

    @if ($errors->has('availability') && $data->hasDates())
    <div class="rounded-lg border border-error/30 bg-error/5 p-4 mt-4">
        <p class="text-sm font-medium text-error">{{ trans('statamic-resrv::frontend.searchError') }}</p>
        <p class="text-sm text-muted">{{ $errors->first('availability') }}</p>
    </div>
    @endif

    <div class="absolute left-0 right-0 top-0 w-full h-full bg-white/60" wire:loading.delay.long>
        <span class="flex items-center justify-center w-full h-full">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="animate-spin w-5 h-5 text-teal">
                <path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0 3.181 3.183a8.25 8.25 0 0 0 13.803-3.7M4.031 9.865a8.25 8.25 0 0 1 13.803-3.7l3.181 3.182m0-4.991v4.99" />
            </svg>
        </span>
    </div>
</div>
