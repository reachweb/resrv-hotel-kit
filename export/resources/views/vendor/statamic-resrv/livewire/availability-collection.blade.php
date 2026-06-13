@use(Carbon\Carbon)
@use(Reach\StatamicResrv\Enums\CancellationPolicy)
@use(Illuminate\Support\Str)

{{-- Restyled to the Resrv Hotel card grid. Markup/classes only — all wire: directives
     and the $data / $this->rows / select() structure are preserved. --}}
<div class="relative">
    @if (! $data->hasDates())
        <div class="rounded-lg border border-line bg-white p-10 text-center">
            <p class="t-h3 mb-2">{{ trans('statamic-resrv::frontend.pleaseSelectDates') }}</p>
            <p class="text-base leading-relaxed text-muted max-w-[44ch] mx-auto">Choose your dates above to see live availability and the best rate for each room.</p>
        </div>
    @else
        @if ($this->rows->isEmpty())
            <div class="rounded-lg border border-line bg-white p-10 text-center">
                <p class="t-h3 mb-2">{{ trans('statamic-resrv::frontend.noAvailability') }}</p>
                <p class="text-base leading-relaxed text-muted">{{ trans('statamic-resrv::frontend.tryAdjustingYourSearch') }}</p>
            </div>
        @else
            <div class="grid gap-6 md:grid-cols-2 lg:grid-cols-3" role="list">
                @foreach ($this->rows as $row)
                    @php($entry = $row['entry'])
                    <article
                        role="listitem"
                        wire:key="resrv-collection-{{ $row['id'] }}"
                        class="bg-white rounded-lg shadow-card overflow-hidden h-full flex flex-col @unless($row['available']) opacity-80 @endunless"
                    >
                        <div class="img-slot relative" style="aspect-ratio: 3/2;">
                            <span class="slot-label">Room image · 3:2</span>
                            @unless ($row['available'])
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-semibold leading-none bg-sand text-muted absolute top-3 left-3 z-10">Sold out</span>
                            @endunless
                        </div>

                        <div class="p-5 lg:p-6 flex flex-col flex-1">
                            <h3 class="t-h3 mb-1.5">
                                @if ($entry->url())
                                    <a href="{{ $entry->url() }}" class="hover:text-terracotta-dark transition-colors">{{ $entry->get('title') }}</a>
                                @else
                                    {{ $entry->get('title') }}
                                @endif
                            </h3>

                            @if ($entry->get('intro'))
                                <p class="text-sm text-muted mb-4">{{ Str::limit(strip_tags((string) $entry->get('intro')), 96) }}</p>
                            @endif

                            <div class="flex flex-wrap gap-x-4 gap-y-1.5 text-sm text-muted mb-5">
                                @if ($entry->get('size_sqm')) <span>{{ $entry->get('size_sqm') }} m²</span> @endif
                                @if ($entry->get('max_occupancy')) <span>{{ $entry->get('max_occupancy') }} guests</span> @endif
                                @if ($entry->get('bed_configuration')) <span>{{ $entry->get('bed_configuration') }}</span> @endif
                            </div>

                            <div class="mt-auto pt-4 border-t border-line">
                                @if ($row['available'])
                                    @if ($showRates)
                                        <div class="flex flex-col gap-2.5">
                                            @foreach ($row['rates'] as $rate)
                                                @php($cancellation = data_get($rate, 'cancellation_policy'))
                                                @php($cancellationLabel = $cancellation ? CancellationPolicy::labelFor($cancellation['policy'], $cancellation['period'], Carbon::parse($data->dates['date_start'])) : null)
                                                <div class="flex items-center justify-between gap-3">
                                                    <div>
                                                        <div class="text-sm font-medium">{{ data_get($this->rateLabels, data_get($rate, 'rate_id'), data_get($rate, 'rateLabel')) }}</div>
                                                        <div class="font-display text-xl text-terracotta-dark leading-none">{{ config('resrv-config.currency_symbol') }}{{ data_get($rate, 'price') }}</div>
                                                        @if ($cancellationLabel)<div class="text-sm text-success mt-0.5">{{ $cancellationLabel }}</div>@endif
                                                    </div>
                                                    <button
                                                        type="button"
                                                        class="inline-flex items-center justify-center gap-2 h-10 px-[18px] rounded-lg border-[1.5px] border-transparent text-sm font-semibold leading-none whitespace-nowrap transition bg-terracotta text-white hover:bg-terracotta-dark focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-terracotta shrink-0"
                                                        wire:click="select('{{ $row['id'] }}', {{ data_get($rate, 'rate_id') ?? 'null' }})"
                                                        aria-label="Book {{ data_get($this->rateLabels, data_get($rate, 'rate_id'), $entry->get('title')) }}"
                                                    >{{ trans('statamic-resrv::frontend.bookNow') }}</button>
                                                </div>
                                            @endforeach
                                        </div>
                                    @else
                                        @php($cancellation = data_get($row['from'], 'cancellation_policy'))
                                        @php($cancellationLabel = $cancellation ? CancellationPolicy::labelFor($cancellation['policy'], $cancellation['period'], Carbon::parse($data->dates['date_start'])) : null)
                                        <div class="flex items-end justify-between gap-3">
                                            <div>
                                                <span class="text-sm text-muted">from</span>
                                                <div class="font-display text-2xl text-terracotta-dark leading-none">
                                                    @if (data_get($row['from'], 'original_price'))
                                                        <span class="text-base text-muted line-through mr-1">{{ config('resrv-config.currency_symbol') }}{{ data_get($row['from'], 'original_price') }}</span>
                                                    @endif
                                                    {{ config('resrv-config.currency_symbol') }}{{ data_get($row['from'], 'price') }}
                                                </div>
                                                @if ($cancellationLabel)<div class="text-sm text-success mt-1">{{ $cancellationLabel }}</div>@endif
                                            </div>
                                            <button
                                                type="button"
                                                class="inline-flex items-center justify-center gap-2 h-10 px-[18px] rounded-lg border-[1.5px] border-transparent text-sm font-semibold leading-none whitespace-nowrap transition bg-terracotta text-white hover:bg-terracotta-dark focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-terracotta shrink-0"
                                                wire:click="select('{{ $row['id'] }}', {{ data_get($row['from'], 'rate_id') ?? 'null' }})"
                                                aria-label="Book {{ $entry->get('title') }}"
                                            >{{ trans('statamic-resrv::frontend.bookNow') }}</button>
                                        </div>
                                    @endif
                                @else
                                    <div class="flex items-center justify-between gap-3">
                                        <span class="text-sm text-muted">{{ trans('statamic-resrv::frontend.noAvailability') }}</span>
                                        @if ($entry->url())<a href="{{ $entry->url() }}" class="inline-flex items-center justify-center gap-2 h-10 px-[18px] rounded-lg border-[1.5px] border-teal text-sm font-semibold leading-none whitespace-nowrap transition bg-transparent text-teal hover:bg-teal hover:text-white focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-terracotta">View</a>@endif
                                    </div>
                                @endif
                            </div>
                        </div>
                    </article>
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
