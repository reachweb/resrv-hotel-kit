@props(['entryRates', 'errors'])

{{-- Restyled to the design's select control. wire:model passthrough unchanged. --}}
<div class="{{ $attributes->get('class') }}">
    <select
        id="availability-search-rate"
        class="select h-12 min-w-[180px] cursor-pointer"
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
    <div class="mt-2 text-error text-sm space-y-1">
        <span class="block">{{ $errors->first('data.rate') }}</span>
    </div>
    @endif
</div>
