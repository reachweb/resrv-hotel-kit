@props(['field', 'key', 'errors'])

{{-- Restyled to the kit's form controls; the wire:model binding is stock.
     Accessibility layer shared by every field view: required fields carry
     aria-required and optional ones (no `required` rule) say "(optional)" after the
     label; an error sets aria-invalid, is linked through aria-describedby and is
     announced (role="alert").
     The placeholder option has an empty value so it matches the empty initial state. --}}
@php
    $handle = $field['handle'];
    $rules = is_array($field['validate'] ?? null) ? $field['validate'] : explode('|', (string) ($field['validate'] ?? ''));
    $isRequired = in_array('required', $rules, true);
    $hasError = $errors->has('form.'.$handle);
    $describedBy = collect([
        array_key_exists('instructions', $field) ? $handle.'-explanation' : null,
        $hasError ? $handle.'-error' : null,
    ])->filter()->join(' ');
    // Statamic stores options either as a key => label map or as a list of {key, value}.
    $options = collect($field['options'] ?? [])->mapWithKeys(fn ($option, $optionKey) => is_array($option)
        ? [$option['key'] => $option['value'] ?? $option['key']]
        : [$optionKey => $option]);
@endphp
<div {{ $attributes->class(['relative', 'md:col-span-2' => $field['width'] === 100, 'md:col-span-1' => $field['width'] === 50,]) }} wire:key="{{ $key }}">
    <label for="{{ $handle }}" class="field-label">
        {{ __($field['display']) }}@unless ($isRequired) <span class="font-normal text-muted">({{ __('optional') }})</span>@endunless
    </label>
    <select
        wire:model="form.{{ $handle }}"
        id="{{ $handle }}"
        name="{{ $handle }}"
        @if ($isRequired) aria-required="true" @endif
        @if ($hasError) aria-invalid="true" @endif
        @if ($describedBy) aria-describedby="{{ $describedBy }}" @endif
        @class(['select', 'has-error' => $hasError])
    >
        <option value="">{{ __('Please select') }}</option>
        @foreach ($options as $optionKey => $optionLabel)
        <option value="{{ $optionKey }}">{{ __($optionLabel) }}</option>
        @endforeach
    </select>
    @if (array_key_exists('instructions', $field))
    <p id="{{ $handle }}-explanation" class="field-help">
        {{ __($field['instructions']) }}
    </p>
    @endif
    @if ($hasError)
    <p id="{{ $handle }}-error" class="field-error" role="alert">{{ implode(', ', $errors->get('form.'.$handle)) }}</p>
    @endif
</div>
