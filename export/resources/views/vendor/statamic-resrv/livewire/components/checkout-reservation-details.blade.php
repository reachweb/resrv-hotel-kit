@use(Carbon\Carbon)
@props(['entry', 'reservation'])

{{-- Restyled to the design's order-summary details (checkout.html): title, rate,
     check-in/out rows with the booking-global times, quantity + cancellation.
     The mobile collapse Alpine behaviour is stock; the toggle reports aria-expanded,
     controls the panel and its text follows the state. The title is an h2 under the
     step's h1. --}}
@php($booking = Statamic\Facades\GlobalSet::findByHandle('booking')?->inDefaultSite())
<div
    x-data="{
        vw: Math.max(document.documentElement.clientWidth || 0, window.innerWidth || 0),
        open: false,
        toggle() {
            this.open = ! this.open;
        },
        updateViewportWidth() {
            this.vw = Math.max(document.documentElement.clientWidth || 0, window.innerWidth || 0);
            this.open = this.vw >= 1024;
        }
    }"
    x-init="() => {
        updateViewportWidth();
    }"
    x-on:resize.window.debounce="updateViewportWidth()"
>
    <h2 class="t-h3 mb-0.5">{{ $entry->title }}</h2>
    @if ($reservation->rate_id)
    <p class="text-sm text-muted">{{ $reservation->getRateLabel() }}</p>
    @endif
    <div wire:ignore id="resrv-reservation-details" x-show="open" x-bind="vw >= 1024 ? '' : { 'x-collapse': '' }">
        <div class="space-y-2 text-sm mt-4 pb-4 border-b border-line">
            @if ($entry->collection()->handle() === 'rooms')
            <div class="flex justify-between gap-3">
                <span class="text-muted">Check-in</span>
                <span class="font-medium text-right">{{ Carbon::parse($reservation->date_start)->format('D j M') }}@if ($booking?->get('check_in_time')) &middot; {{ $booking->get('check_in_time') }}@endif</span>
            </div>
            <div class="flex justify-between gap-3">
                <span class="text-muted">Check-out</span>
                <span class="font-medium text-right">{{ Carbon::parse($reservation->date_end)->format('D j M') }}@if ($booking?->get('check_out_time')) &middot; {{ $booking->get('check_out_time') }}@endif</span>
            </div>
            @else
            <div class="flex justify-between gap-3">
                <span class="text-muted">Date</span>
                <span class="font-medium text-right">{{ Carbon::parse($reservation->date_start)->format('D j M Y') }}</span>
            </div>
            @endif
            @if ($reservation->quantity > 1)
            <div class="flex justify-between gap-3">
                <span class="text-muted">{{ trans('statamic-resrv::frontend.quantity') }}</span>
                <span class="font-medium">x{{ $reservation->quantity }}</span>
            </div>
            @endif
            @php($cancellationLabel = $reservation->cancellationPolicyLabel())
            @if ($cancellationLabel)
            <div class="flex justify-between gap-3">
                <span class="text-muted">{{ trans('statamic-resrv::frontend.cancellationPolicy') }}</span>
                <span class="font-medium text-right">{{ $cancellationLabel }}</span>
            </div>
            @endif
        </div>
    </div>
    <button
        type="button"
        class="mt-3 w-full min-h-6 inline-flex justify-center items-center rounded-md lg:hidden cursor-pointer"
        x-on:click="toggle"
        aria-controls="resrv-reservation-details"
        x-bind:aria-expanded="open.toString()"
    >
        <span class="text-sm font-medium" x-text="open ? @js(trans('Hide details')) : @js(trans('Show details'))">
            {{ trans('Show details') }}
        </span>
        <span class="ml-2 motion-safe:transition-transform" x-bind:class="{ 'rotate-180': open }" aria-hidden="true">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-4">
                <path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5" />
            </svg>
        </span>
    </button>
</div>
