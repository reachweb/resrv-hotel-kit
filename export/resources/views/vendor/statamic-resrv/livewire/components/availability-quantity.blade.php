@props(['maxQuantity', 'errors', 'label' => null])

@php
    $label ??= trans('statamic-resrv::frontend.quantityLabel');
    // The unit the buttons and the announced value use, so their names match the visible
    // label (WCAG 2.5.3): "Guests" → "Fewer guests", "1 guest", "3 guests".
    $unit = \Illuminate\Support\Str::lower($label);
    $unitOne = \Illuminate\Support\Str::singular($unit);
@endphp

{{-- Restyled to the design's stepper (step-btn pair + tabular value). The
     `data.quantity` entangle is unchanged.

     Kit hardening (keep through 3-way merges on upgrade), mirroring the guests popover:
     · a named group (the visible label) around the stepper;
     · at a limit the button is aria-disabled, not disabled, so keyboard focus stays on
       it, and the click is guarded instead;
     · the value is announced as it changes; the icons are hidden from screen readers;
     · a quantity error is role="alert". --}}
<div class="{{ $attributes->get('class') }}">
    <div
        class="flex items-center justify-between gap-4 h-full"
        x-data="{ quantity: $wire.entangle('data.quantity').live, max: {{ (int) $maxQuantity }} }"
        x-id="['quantity-label']"
        role="group"
        x-bind:aria-labelledby="$id('quantity-label')"
    >
        <span x-bind:id="$id('quantity-label')" class="text-xs font-semibold uppercase tracking-widest text-muted">{{ $label }}</span>
        <div class="flex items-center gap-3">
            <button
                type="button"
                class="step-btn"
                x-on:click="if (quantity > 1) quantity--"
                x-bind:aria-disabled="(quantity <= 1).toString()"
                x-bind:class="{'is-disabled': quantity <= 1}"
                aria-label="{{ __('Fewer :unit', ['unit' => $unit]) }}"
            >
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" aria-hidden="true"><path d="M5 12h14"/></svg>
            </button>
            <span class="w-5 text-center tabular-nums font-medium" aria-live="polite" aria-atomic="true"><span x-text="quantity"></span><span class="sr-only" x-text="quantity == 1 ? @js(' '.$unitOne) : @js(' '.$unit)"> {{ $unit }}</span></span>
            <button
                type="button"
                class="step-btn"
                x-on:click="if (quantity < max) quantity++"
                x-bind:aria-disabled="(quantity >= max).toString()"
                x-bind:class="{'is-disabled': quantity >= max}"
                aria-label="{{ __('More :unit', ['unit' => $unit]) }}"
            >
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" aria-hidden="true"><path d="M12 5v14M5 12h14"/></svg>
            </button>
        </div>
    </div>
    @if ($errors->has('data.quantity'))
    <div role="alert" class="mt-2 text-error text-sm space-y-1">
        <span class="block">{{ $errors->first('data.quantity') }}</span>
    </div>
    @endif
</div>
