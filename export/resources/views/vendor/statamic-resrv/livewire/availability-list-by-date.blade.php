@use(Carbon\Carbon)

{{-- Restyled to the design's card language — sittings/rates grouped by date (the
     restaurant's "sittings by date"). The selectDate() wiring is stock. --}}
<div class="relative">
    @if ($availableDates->isNotEmpty())
        <div class="divide-y divide-line">
            <div class="pb-4">
                <h3 class="text-xl font-semibold">{{ trans('statamic-resrv::frontend.availableDates') }}</h3>
                @if ($data->hasDates())
                <p class="text-sm text-muted mt-0.5">
                    {{ trans('statamic-resrv::frontend.availableDatesFrom') }} {{ Carbon::parse($data->dates['date_start'])->format('D j M Y') }}
                </p>
                @endif
            </div>

            {{-- Display cap: the engine returns every future date with availability —
                 show the next 7; re-searching from a later date moves the window. --}}
            @foreach ($availableDates->take(7) as $date => $rateEntries)
                <div class="py-4">
                    <div class="flex items-baseline gap-3 mb-3">
                        <div>
                            <p class="text-xs font-semibold uppercase tracking-[0.12em] text-muted">
                                {{ Carbon::parse($date)->format('D') }}
                            </p>
                            <p class="text-lg font-medium">
                                {{ Carbon::parse($date)->format('j M Y') }}
                            </p>
                        </div>
                        <p class="text-sm text-muted">
                            {{ count($rateEntries) }} {{ trans_choice('statamic-resrv::frontend.optionsAvailable', count($rateEntries)) }}
                        </p>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-2.5">
                        @foreach ($rateEntries as $rateId => $info)
                            <button type="button"
                                 class="flex flex-col p-3.5 bg-white rounded-md border border-line hover:border-ink focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-terracotta transition-colors text-left cursor-pointer"
                                 wire:click="selectDate('{{ $date }}', '{{ $rateId }}')"
                                 wire:key="{{ $date }}-{{ $rateId }}">
                                <span class="text-[15px] font-medium">
                                    {{ $this->entryRates[$rateId] ?? $rateId }}
                                </span>
                                <span class="mt-1 text-sm font-semibold text-terracotta-dark">
                                    {{ config('resrv-config.currency_symbol') }}{{ $info['price'] }}
                                </span>
                                @if ($info['available'] <= 5)
                                    <span class="text-xs text-warning mt-0.5 font-medium">
                                        {{ trans('statamic-resrv::frontend.only') }} {{ $info['available'] }} {{ trans('statamic-resrv::frontend.left') }}
                                    </span>
                                @elseif ($info['available'] > 1)
                                    <span class="text-xs text-muted mt-0.5">
                                        {{ $info['available'] }} {{ trans('statamic-resrv::frontend.available') }}
                                    </span>
                                @endif
                            </button>
                        @endforeach
                    </div>
                </div>
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
