@props(['entryRates', 'errors'])

{{-- Restyled to the design's select control. wire:model passthrough unchanged.
     Kit hardening: the select is named (its first option is only a prompt) and a rate
     error is role="alert". --}}
<div class="{{ $attributes->get('class') }}">
    <select
        id="availability-search-rate"
        class="select h-12 min-w-45 cursor-pointer"
        aria-label="{{ trans('statamic-resrv::frontend.selectRate') }}"
        {{ $attributes->whereStartsWith('wire:model') }}
    >
        <option selected value="any">{{ trans('statamic-resrv::frontend.selectRate') }}</option>
        @foreach ($entryRates as $value => $label)
            <option value="{{ $value }}">
                {{ $label }}
            </option>
        @endforeach
    </select>
    @if ($errors->has('data.rate'))
    <div role="alert" class="mt-2 text-error text-sm space-y-1">
        <span class="block">{{ $errors->first('data.rate') }}</span>
    </div>
    @endif
</div>
