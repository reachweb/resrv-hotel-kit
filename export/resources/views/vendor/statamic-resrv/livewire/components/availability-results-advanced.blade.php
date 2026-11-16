@use(Carbon\Carbon)
@use(Reach\StatamicResrv\Enums\CancellationPolicy)
@props(['availability', 'entryRates', 'dateStart' => null, 'emptyMessage' => null])

{{-- Restyled to the design's rate-comparison rows (room-detail booking panel).
     Markup/classes only — the wire:click="checkoutRate(...)" structure and the
     $availability / $entryRates contract are preserved. The rows are divided, not boxed:
     they sit inside the page's white booking panel, so a bordered white box per rate
     read as a card inside a card. "Book now" keeps its visible text first in the
     accessible name (WCAG 2.5.3) with the rate as an sr-only suffix. --}}
<div {{ $attributes->merge(['class' => 'my-3']) }}>
    @if (count($availability) === 0)
    <div class="rounded-lg bg-sand/60 p-5 text-center">
        <p class="text-sm text-muted">{{ $emptyMessage ?? trans('statamic-resrv::frontend.pleaseSelectDates') }}</p>
    </div>
    @else
    <p class="text-sm font-medium text-muted">
        {{ trans('statamic-resrv::frontend.pleaseSelectRateToBook') }}:
    </p>
    <ul class="divide-y divide-line" role="list">
        @foreach ($availability as $rateId => $data)
            @if (data_get($data, 'message.status') !== true)
            <li class="flex items-baseline justify-between gap-3 py-4 last:pb-0">
                <span class="font-medium text-ui">{{ data_get($entryRates, $rateId) }}</span>
                <span class="text-sm text-muted text-right">{{ trans('statamic-resrv::frontend.noAvailability') }}</span>
            </li>
            @else
            @php($cancellation = data_get($data, 'data.cancellation_policy'))
            @php($cancellationLabel = ($cancellation && $dateStart) ? CancellationPolicy::labelFor($cancellation['policy'], $cancellation['period'], Carbon::parse($dateStart)) : null)
            @php($freeCancellationOpen = $cancellationLabel && data_get($cancellation, 'policy') !== 'non_refundable' && CancellationPolicy::freeCancellationOpen($cancellation['period'], Carbon::parse($dateStart)))
            @php($days = (int) data_get($data, 'request.days', 0))
            @php($perNight = $days > 0 ? round(((float) data_get($data, 'data.price')) / $days) : null)
            <li class="py-4 last:pb-0">
                <div class="flex items-start justify-between gap-3">
                    <span class="font-medium text-ui">{{ data_get($entryRates, $rateId) }}</span>
                    @if ($perNight)
                    <span class="text-right shrink-0">
                        <span class="font-display text-xl text-terracotta-dark leading-none tabular-nums">{{ config('resrv-config.currency_symbol') }}{{ $perNight }}</span>
                        @if ($days > 1)<span class="text-sm text-muted">/ night</span>@endif
                    </span>
                    @endif
                </div>
                {{-- The policy badge gets the full row: a dated label is long, and a pill squeezed
                     beside the button wrapped into a lozenge. rounded-lg is a pill on one line and a
                     soft block when it does wrap. --}}
                @if ($cancellationLabel)
                <div class="mt-2">
                    @if ($freeCancellationOpen)
                    <span class="inline-flex items-start gap-1.5 px-2.5 py-1 rounded-lg text-xs font-semibold leading-tight bg-success/10 text-success">
                        <svg class="size-3 shrink-0 mt-px" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M20 6 9 17l-5-5"/></svg>
                        {{ $cancellationLabel }}
                    </span>
                    @else
                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg text-xs font-semibold leading-tight bg-sand text-muted">{{ $cancellationLabel }}</span>
                    @endif
                </div>
                @endif
                <div class="flex items-center justify-between gap-3 mt-3">
                    <div class="text-sm text-muted tabular-nums">
                        @if ($days > 1){{ config('resrv-config.currency_symbol') }}{{ data_get($data, 'data.price') }} total @endif
                    </div>
                    <x-resrv::kit-cta size="sm" class="shrink-0" wire:click="checkoutRate('{{ $rateId }}')">{{ trans('statamic-resrv::frontend.bookNow') }}<span class="sr-only">: {{ data_get($entryRates, $rateId) }}</span></x-resrv::kit-cta>
                </div>
            </li>
            @endif
        @endforeach
    </ul>
    @endif
</div>
