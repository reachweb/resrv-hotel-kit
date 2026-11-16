@props(['field', 'key', 'errors'])

{{-- Restyled to the kit's form controls; the wire:model binding is stock.
     Accessibility layer shared by every field view: required fields carry
     aria-required and optional ones (no `required` rule) say "(optional)" after the
     label; an error sets aria-invalid, is linked through aria-describedby and is
     announced (role="alert").
     The group is a <fieldset> named by its <legend>, each checkbox has its own
     field-scoped id, and @tailwindcss/forms draws it in Terracotta Dark. --}}
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
<fieldset {{ $attributes->class(['relative min-w-0', 'md:col-span-2' => $field['width'] === 100, 'md:col-span-1' => $field['width'] === 50,]) }} wire:key="{{ $key }}" @if ($describedBy) aria-describedby="{{ $describedBy }}" @endif>
    <legend class="field-label">
        {{ __($field['display']) }}@unless ($isRequired) <span class="font-normal text-muted">({{ __('optional') }})</span>@endunless
    </legend>
    @foreach ($options as $optionKey => $optionLabel)
    <div class="flex items-center mb-3">
        <input
            type="checkbox"
            wire:model="form.{{ $handle }}"
            name="{{ $handle }}[]"
            id="{{ $handle }}-{{ $loop->index }}"
            value="{{ $optionKey }}"
            @if ($hasError) aria-invalid="true" @endif
            class="size-4 rounded-sm border-line-strong text-terracotta-dark focus:ring-terracotta-dark cursor-pointer"
        >
        <label for="{{ $handle }}-{{ $loop->index }}" class="ms-2.5 text-ui cursor-pointer">{{ __($optionLabel) }}</label>
    </div>
    @endforeach
    @if (array_key_exists('instructions', $field))
    <p id="{{ $handle }}-explanation" class="field-help">
        {{ __($field['instructions']) }}
    </p>
    @endif
    @if ($hasError)
    <p id="{{ $handle }}-error" class="field-error" role="alert">{{ implode(', ', $errors->get('form.'.$handle)) }}</p>
    @endif
</fieldset>
