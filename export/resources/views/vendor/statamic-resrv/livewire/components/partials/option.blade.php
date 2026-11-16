{{-- Restyled to the design's segmented option control (.seg / .seg-btn pills; the
     selected state is driven by the hidden radio via has-[:checked]). The
     selectOption() wiring and required/description logic are stock — a stray
     </span> in the stock view (option name) was also removed. The pills sit in a
     <fieldset> whose <legend> is the option name (and Required badge), and the pill
     draws the focus ring for its sr-only radio. --}}
<fieldset wire:key="option-{{ $option->id }}" class="min-w-0" @if ($option->description) aria-describedby="resrv-option-{{ $option->id }}-desc" @endif>
    <legend class="w-full mb-3">
        <span class="flex flex-wrap items-center justify-between gap-2">
            <span class="font-medium">{{ $option->name }}</span>
            @if ($option->required)
            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-semibold leading-none bg-sand text-muted uppercase tracking-wide">
                {{ trans('statamic-resrv::frontend.required') }}
            </span>
            @endif
        </span>
    </legend>
    @if ($option->description)
    <p class="text-sm text-muted -mt-2 mb-3" id="resrv-option-{{ $option->id }}-desc">{{ $option->description }}</p>
    @endif

    <div class="seg">
        @foreach ($option->values as $value)
        <label
            wire:key="{{ $value->id }}"
            wire:loading.class="opacity-50 pointer-events-none"
            for="{{ $option->slug }}-{{ $value->id }}"
            class="seg-btn cursor-pointer transition-opacity duration-150 has-[:checked]:bg-teal has-[:checked]:border-teal has-[:checked]:text-white has-[:focus-visible]:outline-2 has-[:focus-visible]:outline-offset-2 has-[:focus-visible]:outline-terracotta-dark"
        >
            <input
                type="radio"
                name="{{ $option->slug }}"
                id="{{ $option->slug }}-{{ $value->id }}"
                wire:change.throttle="selectOption({{ $option->id }}, {{ $value->id }})"
                value="{{ $value->id }}"
                class="sr-only"
                @if ($this->isOptionValueSelected($option->id, $value->id)) checked @endif
            />
            {{ $value->name }}
            @if ($value->price_type !== 'free')
                <span class="opacity-70 ml-1">+{{ config('resrv-config.currency_symbol') }}{{ $value->price->format() }}</span>
            @endif
            @if ($value->description)
                <span class="sr-only">{{ $value->description }}</span>
            @endif
        </label>
        @endforeach
    </div>
</fieldset>
