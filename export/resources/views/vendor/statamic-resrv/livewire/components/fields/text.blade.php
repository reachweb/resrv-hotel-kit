@props(['field', 'key', 'errors'])

@unless (array_key_exists('input_type', $field) && $field['input_type'] === 'hidden')
<div {{ $attributes->class(['relative', 'md:col-span-2' => $field['width'] === 100, 'md:col-span-1' => $field['width'] === 50,]) }} wire:key="{{ $key }}">
    <label for="{{ $field['handle'] }}" class="field-label">
        {{ __($field['display']) }}
    </label>
    <input
        wire:model="form.{{ $field['handle'] }}"
        type="text"
        id="{{ $field['handle'] }}"
        @class(['input', 'has-error' => $errors->has('form.' . $field['handle'])])
        @if (array_key_exists('instructions', $field))
        aria-describedby="{{ $field['handle'] }}-explanation"
        @endif
    />
    @if (array_key_exists('instructions', $field))
    <p id="{{ $field['handle'] }}-explanation" class="field-help">
        {{ __($field['instructions']) }}
    </p>
    @endif
    @if ($errors->has('form.' . $field['handle']))
    <p class="field-error">{{ implode(', ', $errors->get('form.' . $field['handle'])) }}</p>
    @endif
</div>
@endunless
