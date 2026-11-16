@php
    $quantity = $this->getExtraQuantity($extra->id);
    $extraName = $extra->override_label ?? $extra->name;
    $atMinimum = $quantity <= 1;
    $atMaximum = $extra->maximum && $quantity >= $extra->maximum;
@endphp

{{-- Restyled to the design's stepper (step-btn pair). updateExtraQuantity wiring stock.
     Mirrors the guests stepper: buttons name the extra, a limit is aria-disabled (not
     disabled, so focus stays put) and its click isn't bound, and the count is announced. --}}
<div @class([
    'flex items-center gap-2.5',
    'hidden' => ! $this->isExtraSelected($extra->id),
])>
    <button
        type="button"
        @unless ($atMinimum) wire:click.throttle="updateExtraQuantity({{ $extra->id }}, {{ $quantity - 1 }})" @endunless
        aria-disabled="{{ $atMinimum ? 'true' : 'false' }}"
        class="step-btn aria-disabled:cursor-not-allowed aria-disabled:hover:border-line aria-disabled:[&_svg]:opacity-35"
        aria-label="{{ __('Decrease quantity') }}, {{ $extraName }}"
    >
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" aria-hidden="true"><path d="M5 12h14"/></svg>
    </button>
    <span class="w-5 text-center tabular-nums font-medium" aria-live="polite" aria-atomic="true">{{ $quantity }}<span class="sr-only"> &times; {{ $extraName }}</span></span>
    <button
        type="button"
        @unless ($atMaximum) wire:click.throttle="updateExtraQuantity({{ $extra->id }}, {{ $quantity + 1 }})" @endunless
        aria-disabled="{{ $atMaximum ? 'true' : 'false' }}"
        class="step-btn aria-disabled:cursor-not-allowed aria-disabled:hover:border-line aria-disabled:[&_svg]:opacity-35"
        aria-label="{{ __('Increase quantity') }}, {{ $extraName }}"
    >
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" aria-hidden="true"><path d="M12 5v14M5 12h14"/></svg>
    </button>
</div>
