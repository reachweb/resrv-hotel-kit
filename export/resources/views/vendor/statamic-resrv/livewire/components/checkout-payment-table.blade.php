@use(Reach\StatamicResrv\Facades\Price)
@props(['extras', 'enabledExtras', 'options', 'enabledOptions', 'totals', 'key'])

{{-- Restyled to the design's order-summary line items + totals (checkout.html).
     Reads as a receipt: the reservation line, any discount / options / extras, then
     the Total. With nothing between them the reservation line would just repeat the
     Total, so only the Total shows; likewise "Payable now" only when it differs from
     the Total. The discount is Resrv's own originalPrice (coupon or dynamic-pricing
     campaign) minus the reservation price. The payment surcharge is not part of the
     Total, so it sits under it with "Payable now", as upstream. Totals logic and the
     wire:key structure are stock. The running total is announced from checkout's own
     polite live region: this table is re-keyed (so replaced, not updated) whenever the
     chosen extras or options change, and a live region that is replaced stays silent. --}}
@php($currency = config('resrv-config.currency_symbol'))
@php($reservationTotal = $totals->get('reservationTotal'))
@php($originalPrice = $totals->get('originalPrice'))
@php($discount = $originalPrice && $originalPrice->greaterThan($reservationTotal) ? Price::create($originalPrice->format())->subtract($reservationTotal) : null)
@php($hasSurcharge = $totals->has('paymentSurcharge') && ! $totals->get('paymentSurcharge')->isZero())
@php($hasBreakdown = $discount || $enabledOptions->options->count() > 0 || $enabledExtras->extras->count() > 0)
@php($payableNow = Price::create($totals->get('payment')->format())->add($totals->get('paymentSurcharge')))
<div {{ $attributes->merge(['class' => 'flex flex-grow']) }} wire:key="{{ $key }}">
    <div class="flex flex-grow flex-col justify-between">
        @if ($hasBreakdown)
        <div class="space-y-2 text-sm py-4 border-b border-line">
            <div class="flex justify-between gap-3">
                <span class="text-muted">{{ trans('statamic-resrv::frontend.reservationTotal') }}</span>
                <span class="tabular-nums">{{ $currency }}{{ ($discount ? $originalPrice : $reservationTotal)->format() }}</span>
            </div>
            @if ($discount)
            <div class="flex justify-between gap-3">
                <span class="text-muted">Discount</span>
                <span class="tabular-nums text-success">&minus;{{ $currency }}{{ $discount->format() }}</span>
            </div>
            @endif
            @foreach ($enabledOptions->options as $option)
            <div class="flex justify-between gap-3" wire:key="{{ $option['id'] }}">
                <span class="text-muted">{{ $option['optionName'] }}: {{ $option['valueName'] }}</span>
                @if ($option['price'] != 0)
                <span class="tabular-nums">{{ $currency }}{{ $option['price'] }}</span>
                @else
                <span class="text-success">{{ ucfirst(trans('statamic-resrv::frontend.free')) }}</span>
                @endif
            </div>
            @endforeach
            @foreach ($enabledExtras->extras as $extra)
            <div class="flex justify-between gap-3" wire:key="{{ $extra['id'] }}">
                <span class="text-muted">{{ $extra['name'] }}@if ($extra['quantity'] > 1) ×{{ $extra['quantity'] }}@endif</span>
                <span class="tabular-nums">{{ $currency }}{{ $enabledExtras->lineTotal($extra)->format() }}</span>
            </div>
            @endforeach
        </div>
        @endif
        <div>
            <div class="flex items-baseline justify-between gap-3 pt-4">
                <span class="text-xl font-semibold font-display">{{ trans('statamic-resrv::frontend.total') }}</span>
                <span class="font-display text-3xl tabular-nums leading-none">{{ $currency }}{{ $totals->get('total')->format() }}</span>
            </div>
            @if ($hasSurcharge)
            <div class="flex justify-between gap-3 text-sm mt-2">
                <span class="text-muted">{{ trans('statamic-resrv::frontend.paymentSurcharge') }}</span>
                <span class="tabular-nums">+{{ $currency }}{{ $totals->get('paymentSurcharge')->format() }}</span>
            </div>
            @endif
            @if (((config('resrv-config.payment') !== 'everything' && $this->freeCancellationPossible()) || $hasSurcharge) && ! $payableNow->equals($totals->get('total')))
            <div class="flex justify-between gap-3 text-sm mt-2">
                <span class="text-muted">{{ trans('statamic-resrv::frontend.payableNow') }}</span>
                <span class="font-medium tabular-nums">{{ $currency }}{{ $payableNow->format() }}</span>
            </div>
            @endif
            <p class="text-sm text-muted mt-1.5">Incl. all taxes &middot; {{ config('resrv-config.currency_isoCode', 'EUR') }}</p>
        </div>
    </div>
</div>
