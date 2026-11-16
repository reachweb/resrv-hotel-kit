@use(Carbon\Carbon)
@use(Reach\StatamicResrv\Enums\CancellationPolicy)
{{-- Restyled to the kit's summary rows (shown by availability-results when a single rate
     is chosen): muted labels, Ink values, the total in Terracotta Dark, tabular figures.
     Markup/classes only — the $this->calculateAvailabilityTotals() / freeCancellationPossible()
     calls and the payment / cancellation conditions are stock. The cancellation line is
     Success only while free cancellation is still open, like the rate rows. --}}
<div class="font-medium mb-2">{{ trans('statamic-resrv::frontend.paymentDetails') }}</div>
<dl class="text-sm space-y-2">
    <div class="flex items-baseline justify-between gap-4">
        <dt class="text-muted">{{ trans('statamic-resrv::frontend.totalAmount') }}</dt>
        <dd class="font-display text-xl leading-none text-terracotta-dark tabular-nums whitespace-nowrap">{{ config('resrv-config.currency_symbol') }}{{ $this->calculateAvailabilityTotals($availability->get('data')['price'])->format() }}</dd>
    </div>
    @if (config('resrv-config.payment') !== 'everything' && $this->freeCancellationPossible($availability->get('data')['rate_id'] ?? null))
    <div class="flex items-baseline justify-between gap-4">
        <dt class="text-muted">{{ trans('statamic-resrv::frontend.payableNow') }}</dt>
        <dd class="font-medium text-ink tabular-nums whitespace-nowrap">{{ config('resrv-config.currency_symbol') }}{{ $this->calculateAvailabilityTotals($availability->get('data')['payment'])->format() }}</dd>
    </div>
    @endif
    @php($cancellation = $availability->get('data')['cancellation_policy'] ?? null)
    @php($cancellationLabel = $cancellation ? CancellationPolicy::labelFor($cancellation['policy'], $cancellation['period'], Carbon::parse($this->data->dates['date_start'])) : null)
    @if ($cancellationLabel)
    @php($freeCancellationOpen = $cancellation['policy'] !== 'non_refundable' && CancellationPolicy::freeCancellationOpen($cancellation['period'], Carbon::parse($this->data->dates['date_start'])))
    <div class="flex items-baseline justify-between gap-4">
        <dt class="text-muted shrink-0">{{ trans('statamic-resrv::frontend.cancellationPolicy') }}</dt>
        <dd @class(['font-medium text-right', 'text-success' => $freeCancellationOpen, 'text-ink' => ! $freeCancellationOpen])>{{ $cancellationLabel }}</dd>
    </div>
    @endif
</dl>
