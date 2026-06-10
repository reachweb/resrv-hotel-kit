@props(['maxQuantity', 'errors', 'label' => null])

{{-- Restyled to the design's stepper (step-btn pair + tabular value). The
     `data.quantity` entangle is unchanged. --}}
<div class="{{ $attributes->get('class') }}">
    <div class="flex items-center justify-between gap-4 h-full" x-data="{ quantity: $wire.entangle('data.quantity').live }">
        <span class="text-xs font-semibold uppercase tracking-[0.12em] text-muted">{{ $label ?? trans('statamic-resrv::frontend.quantityLabel') }}</span>
        <div class="flex items-center gap-3">
            <button
                type="button"
                class="step-btn"
                x-on:click="quantity--"
                x-bind:disabled="quantity === 1"
                x-bind:class="{'is-disabled': quantity === 1}"
                aria-label="Decrease quantity"
            >
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6"><path d="M5 12h14"/></svg>
            </button>
            <span class="w-5 text-center tabular-nums font-medium" x-text="quantity"></span>
            <button
                type="button"
                class="step-btn"
                x-on:click="quantity++"
                x-bind:disabled="quantity === {{ $maxQuantity }}"
                x-bind:class="{'is-disabled': quantity === {{ $maxQuantity }}}"
                aria-label="Increase quantity"
            >
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6"><path d="M12 5v14M5 12h14"/></svg>
            </button>
        </div>
    </div>
    @if ($errors->has('data.quantity'))
    <div class="mt-2 text-error text-sm space-y-1">
        <span class="block">{{ $errors->first('data.quantity') }}</span>
    </div>
    @endif
</div>
