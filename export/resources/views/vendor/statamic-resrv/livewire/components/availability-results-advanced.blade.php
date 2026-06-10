@use(Carbon\Carbon)
@use(Reach\StatamicResrv\Enums\CancellationPolicy)
@props(['availability', 'entryRates', 'dateStart' => null])

{{-- Restyled to the design's rate-comparison rows (room-detail booking panel).
     Markup/classes only — the wire:click="checkoutRate(...)" structure and the
     $availability / $entryRates contract are preserved. --}}
<div {{ $attributes->merge(['class' => 'my-3']) }}>
    @if (count($availability) === 0)
    <div class="rounded-lg border border-line bg-sand/60 p-5 text-center">
        <span class="text-sm text-muted">{{ trans('statamic-resrv::frontend.pleaseSelectDates') }}</span>
    </div>
    @else
    <div class="text-sm font-medium text-muted mb-3">
        {{ trans('statamic-resrv::frontend.pleaseSelectRateToBook') }}:
    </div>
    <div class="grid grid-cols-1 gap-2.5" role="list">
        @foreach ($availability as $rateId => $data)
            @if (data_get($data, 'message.status') !== true)
            <div class="rounded-lg border border-line bg-sand/60 p-4 text-center" role="listitem">
                <div class="font-medium text-[15px]">{{ data_get($entryRates, $rateId) }}</div>
                <div class="text-sm text-muted mt-1">{{ trans('statamic-resrv::frontend.noAvailability') }}</div>
            </div>
            @else
            @php($cancellation = data_get($data, 'data.cancellation_policy'))
            @php($cancellationLabel = ($cancellation && $dateStart) ? CancellationPolicy::labelFor($cancellation['policy'], $cancellation['period'], Carbon::parse($dateStart)) : null)
            @php($nonRefundable = data_get($cancellation, 'policy') === 'non_refundable')
            @php($days = (int) data_get($data, 'request.days', 0))
            @php($perNight = $days > 0 ? round(((float) data_get($data, 'data.price')) / $days) : null)
            <div class="rounded-lg border border-line bg-white p-4 transition-colors hover:border-sage" role="listitem">
                <div class="flex items-start justify-between gap-3">
                    <span class="font-medium text-[15px]">{{ data_get($entryRates, $rateId) }}</span>
                    @if ($perNight)
                    <span class="text-right shrink-0">
                        <span class="font-display text-[20px] text-terracotta-dark leading-none">{{ config('resrv-config.currency_symbol') }}{{ $perNight }}</span>
                        @if ($days > 1)<span class="text-sm text-muted">/night</span>@endif
                    </span>
                    @endif
                </div>
                <div class="flex items-end justify-between gap-3 mt-2">
                    <div>
                        @if ($cancellationLabel)
                            @if ($nonRefundable)
                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-semibold leading-none bg-sand text-muted">{{ $cancellationLabel }}</span>
                            @else
                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-semibold leading-none bg-success/10 text-success">
                                <svg class="w-3 h-3" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6 9 17l-5-5"/></svg>
                                {{ $cancellationLabel }}
                            </span>
                            @endif
                        @endif
                        @if ($days > 1)
                        <div class="text-sm text-muted mt-1.5 tabular-nums">{{ config('resrv-config.currency_symbol') }}{{ data_get($data, 'data.price') }} total</div>
                        @endif
                    </div>
                    <button
                        type="button"
                        class="inline-flex items-center justify-center gap-2 h-10 px-[18px] rounded-lg border-[1.5px] border-transparent text-sm font-semibold leading-none whitespace-nowrap transition bg-terracotta text-white hover:bg-terracotta-dark focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-terracotta shrink-0"
                        wire:click="checkoutRate('{{ $rateId }}')"
                        aria-label="Book {{ data_get($entryRates, $rateId) }} now"
                    >
                        {{ trans('statamic-resrv::frontend.bookNow') }}
                    </button>
                </div>
            </div>
            @endif
        @endforeach
    </div>
    @endif
</div>
