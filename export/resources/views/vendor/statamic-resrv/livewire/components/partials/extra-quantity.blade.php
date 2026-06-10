@php
    $quantity = $this->getExtraQuantity($extra->id);
@endphp

{{-- Restyled to the design's stepper (step-btn pair). updateExtraQuantity wiring stock. --}}
<div @class([
    'flex items-center gap-2.5',
    'hidden' => ! $this->isExtraSelected($extra->id),
])>
    <button
        type="button"
        wire:click.throttle="updateExtraQuantity({{ $extra->id }}, {{ $quantity - 1 }})"
        @if ($quantity <= 1) disabled @endif
        @class(['step-btn', 'is-disabled' => $quantity <= 1])
        aria-label="Decrease quantity"
    >
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6"><path d="M5 12h14"/></svg>
    </button>
    <span class="w-5 text-center tabular-nums font-medium">{{ $quantity }}</span>
    <button
        type="button"
        wire:click.throttle="updateExtraQuantity({{ $extra->id }}, {{ $quantity + 1 }})"
        @if ($extra->maximum && $quantity >= $extra->maximum) disabled @endif
        @class(['step-btn', 'is-disabled' => $extra->maximum && $quantity >= $extra->maximum])
        aria-label="Increase quantity"
    >
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6"><path d="M12 5v14M5 12h14"/></svg>
    </button>
</div>
