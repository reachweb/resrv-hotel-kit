{{-- Restyled to the design's extra card (checkout.html): name + description, price
     label with a "for your stay"/each suffix (a per-day extra's price is already
     multiplied by the nights, so it is never a nightly rate), brand toggle (teal),
     and the quantity stepper for allow_multiple extras. All wire: directives
     (toggleExtra, hiddenExtras, requiredExtras) are stock — markup/classes only. The
     checkbox takes its name from the extra's name (aria-labelledby) and its
     description, Required badge and price from aria-describedby; the off track is
     Line Strong (3:1) and the track draws the focus ring for the sr-only input. --}}
@php($extraId = 'resrv-extra-'.$extra->id)
@php($isRequired = $this->requiredExtras->contains($extra->id))
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
                <span class="font-medium" id="{{ $extraId }}-name">{{ $extra->override_label ?? $extra->name }}</span>
                @if ($isRequired)
                <span id="{{ $extraId }}-required" class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold leading-none bg-sand text-muted uppercase tracking-wide">
                    {{ trans('statamic-resrv::frontend.required') }}
                </span>
                @endif
            </div>
            @if ($extra->description)
            <p class="text-sm text-muted mt-0.5" id="{{ $extraId }}-desc">{{ $extra->description }}</p>
            @endif
        </div>
        <label class="inline-flex items-center cursor-pointer shrink-0 mt-0.5">
            <input
                type="checkbox"
                class="sr-only peer"
                aria-labelledby="{{ $extraId }}-name"
                aria-describedby="{{ collect([$isRequired ? $extraId.'-required' : null, $extra->description ? $extraId.'-desc' : null, $extraId.'-price'])->filter()->join(' ') }}"
                wire:change.throttle="toggleExtra({{ $extra->id }})"
                {{ $isRequired ? 'disabled' : '' }}
                {{ $this->isExtraSelected($extra->id) ? 'checked' : '' }}>
                <span
                    aria-hidden="true"
                    class="relative block shrink-0 w-11 h-6 rounded-full bg-line-strong transition-[background-color] motion-reduce:transition-none peer-checked:bg-teal
                    peer-focus-visible:outline-2 peer-focus-visible:outline-offset-2 peer-focus-visible:outline-terracotta-dark
                    after:content-[''] after:absolute after:top-0.5 after:start-0.5 after:size-5 after:rounded-full
                    after:bg-white after:border after:border-line after:transition-transform motion-reduce:after:transition-none
                    peer-checked:after:translate-x-full peer-checked:after:border-white
                    {{ $isRequired ? 'cursor-not-allowed' : '' }}"
                ></span>
        </label>
    </div>
    <div class="flex items-center justify-between gap-3 mt-3 pt-3 border-t border-line">
        <span class="text-sm font-semibold text-terracotta-dark" id="{{ $extraId }}-price">
            {{ config('resrv-config.currency_symbol') }}{{ $extra->price }}@if ($extra->price_type === 'perday')<span class="font-normal text-muted"> for your stay</span>@elseif ($extra->allow_multiple)<span class="font-normal text-muted"> each</span>@endif
        </span>
        @if ($extra->allow_multiple)
            @include('statamic-resrv::livewire.components.partials.extra-quantity')
        @endif
    </div>
</div>
