{{-- Restyled to the design's extra card (checkout.html): name + description, price
     label with per-night/each suffix, brand toggle (teal), and the quantity stepper
     for allow_multiple extras. All wire: directives (toggleExtra, hiddenExtras,
     requiredExtras) are stock — markup/classes only. --}}
<div
    wire:key="extra-{{ $extra->id }}"
    wire:loading.class="opacity-50 pointer-events-none"
    @class([
        'bg-white rounded-lg shadow-card p-4 lg:p-5 flex flex-col transition-opacity duration-150',
        'hidden' => $this->hiddenExtras->contains($extra->id)
    ])
>
    <div class="flex items-start justify-between gap-3">
        <div class="min-w-0">
            <div class="flex items-center gap-2 flex-wrap">
                <span class="font-medium">{{ $extra->override_label ?? $extra->name }}</span>
                @if ($this->requiredExtras->contains($extra->id))
                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold leading-none bg-sand text-muted uppercase tracking-wide">
                    {{ trans('statamic-resrv::frontend.required') }}
                </span>
                @endif
            </div>
            @if ($extra->description)
            <p class="text-sm text-muted mt-0.5">{{ $extra->description }}</p>
            @endif
        </div>
        <label class="inline-flex items-center cursor-pointer shrink-0 mt-0.5">
            <input
                type="checkbox"
                class="sr-only peer"
                wire:change.throttle="toggleExtra({{ $extra->id }})"
                {{ $this->requiredExtras->contains($extra->id) ? 'disabled' : '' }}
                {{ $this->isExtraSelected($extra->id) ? 'checked' : '' }}>
                <div
                    class="relative flex-shrink-0 w-11 h-6 bg-line peer-focus:outline-none peer-focus:ring-2
                    peer-focus:ring-terracotta/40 rounded-full peer peer-checked:after:translate-x-full
                    peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px]
                    after:start-[2px] after:bg-white after:border-line after:border after:rounded-full
                    after:w-5 after:h-5 after:transition-all peer-checked:bg-teal
                    {{ $this->requiredExtras->contains($extra->id) ? 'cursor-not-allowed' : '' }}"
                >
            </div>
        </label>
    </div>
    <div class="flex items-center justify-between gap-3 mt-3 pt-3 border-t border-line">
        <span class="text-sm font-semibold text-terracotta-dark">
            {{ config('resrv-config.currency_symbol') }}{{ $extra->price }}@if ($extra->price_type === 'perday')<span class="font-normal text-muted"> / night</span>@elseif ($extra->allow_multiple)<span class="font-normal text-muted"> each</span>@endif
        </span>
        @if ($extra->allow_multiple)
            @include('statamic-resrv::livewire.components.partials.extra-quantity')
        @endif
    </div>
</div>
