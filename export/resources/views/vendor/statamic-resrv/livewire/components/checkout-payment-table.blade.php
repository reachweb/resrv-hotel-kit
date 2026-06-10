@props(['extras', 'enabledExtras', 'options', 'enabledOptions', 'totals', 'key'])

{{-- Restyled to the design's order-summary line items + totals (checkout.html).
     All totals logic and wire:key structure are stock — markup/classes only. --}}
<div {{ $attributes->merge(['class' => 'flex flex-grow']) }} wire:key="{{ $key }}">
    <div class="flex flex-grow flex-col justify-between">
        @if ($enabledOptions->options->count() > 0 || $enabledExtras->extras->count() > 0)
        <div class="space-y-2 text-sm py-4 border-b border-line">
            @if ($enabledOptions->options->count() > 0)
                @foreach ($enabledOptions->options as $option)
                <div class="flex justify-between gap-3" wire:key="{{ $option['id'] }}">
                    <span class="text-muted">{{ $option['optionName'] }}: {{ $option['valueName'] }}</span>
                    @if ($option['price'] != 0)
                    <span class="tabular-nums">{{ config('resrv-config.currency_symbol') }}{{ $option['price'] }}</span>
                    @else
                    <span class="text-success">{{ ucfirst(trans('statamic-resrv::frontend.free')) }}</span>
                    @endif
                </div>
                @endforeach
            @endif
            @if ($enabledExtras->extras->count() > 0)
                @foreach ($enabledExtras->extras as $extra)
                <div class="flex justify-between gap-3" wire:key="{{ $extra['id'] }}">
                    <span class="text-muted">{{ $extra['name'] }}@if ($extra['quantity'] > 1) ×{{ $extra['quantity'] }}@endif</span>
                    <span class="tabular-nums">{{ config('resrv-config.currency_symbol') }}{{ $extra['price'] * $extra['quantity'] }}</span>
                </div>
                @endforeach
            @endif
        </div>
        @endif
        <div>
            <div class="space-y-2 text-sm py-4 border-b border-line">
                <div class="flex justify-between gap-3">
                    <span class="text-muted">{{ trans('statamic-resrv::frontend.reservationTotal') }}</span>
                    <span class="tabular-nums">{{ config('resrv-config.currency_symbol') }}{{ $totals->get('reservationTotal')->format() }}</span>
                </div>
                @if ($totals->has('paymentSurcharge') && ! $totals->get('paymentSurcharge')->isZero())
                <div class="flex justify-between gap-3">
                    <span class="text-muted">{{ trans('statamic-resrv::frontend.paymentSurcharge') }}</span>
                    <span class="tabular-nums">+{{ config('resrv-config.currency_symbol') }}{{ $totals->get('paymentSurcharge')->format() }}</span>
                </div>
                @endif
            </div>
            <div class="flex items-baseline justify-between gap-3 pt-4">
                <span class="text-xl font-semibold font-display">{{ trans('statamic-resrv::frontend.total') }}</span>
                <span class="font-display text-[28px] tabular-nums leading-none">{{ config('resrv-config.currency_symbol') }}{{ $totals->get('total')->format() }}</span>
            </div>
            @if ((config('resrv-config.payment') !== 'everything' && $this->freeCancellationPossible()) || ($totals->has('paymentSurcharge') && ! $totals->get('paymentSurcharge')->isZero()))
            <div class="flex justify-between gap-3 text-sm mt-2">
                <span class="text-muted">{{ trans('statamic-resrv::frontend.payableNow') }}</span>
                <span class="font-medium tabular-nums">{{ config('resrv-config.currency_symbol') }}{{ $totals->get('payment')->add($totals->get('paymentSurcharge'))->format() }}</span>
            </div>
            @endif
            <p class="text-sm text-muted mt-1.5">Incl. all taxes &middot; {{ config('resrv-config.currency_isoCode', 'EUR') }}</p>
        </div>
    </div>
</div>
