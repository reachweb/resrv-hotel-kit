@props(['field', 'key', 'errors'])

{{-- Restyled to the kit's form controls; the wire:model binding is stock.
     Accessibility layer shared by every field view: required fields carry
     aria-required and optional ones (no `required` rule) say "(optional)" after the
     label; an error sets aria-invalid, is linked through aria-describedby and is
     announced (role="alert"). --}}
@php
    $handle = $field['handle'];
    $rules = is_array($field['validate'] ?? null) ? $field['validate'] : explode('|', (string) ($field['validate'] ?? ''));
    $isRequired = in_array('required', $rules, true);
    $hasError = $errors->has('form.'.$handle);
    $describedBy = collect([
        array_key_exists('instructions', $field) ? $handle.'-explanation' : null,
        $hasError ? $handle.'-error' : null,
    ])->filter()->join(' ');
@endphp
<div {{ $attributes->class(['relative', 'md:col-span-2' => $field['width'] === 100, 'md:col-span-1' => $field['width'] === 50,]) }} wire:key="{{ $key }}">
    <label for="{{ $handle }}" class="field-label">
        {{ __($field['display']) }}@unless ($isRequired) <span class="font-normal text-muted">({{ __('optional') }})</span>@endunless
    </label>
    <input
        wire:model="form.{{ $handle }}"
        type="number"
        inputmode="numeric"
        id="{{ $handle }}"
        name="{{ $handle }}"
        @if ($isRequired) aria-required="true" @endif
        @if ($hasError) aria-invalid="true" @endif
        @if ($describedBy) aria-describedby="{{ $describedBy }}" @endif
        @class(['input', 'has-error' => $hasError])
    />
    @if (array_key_exists('instructions', $field))
    <p id="{{ $handle }}-explanation" class="field-help">
        {{ __($field['instructions']) }}
    </p>
    @endif
    @if ($hasError)
    <p id="{{ $handle }}-error" class="field-error" role="alert">{{ implode(', ', $errors->get('form.'.$handle)) }}</p>
    @endif
</div>
