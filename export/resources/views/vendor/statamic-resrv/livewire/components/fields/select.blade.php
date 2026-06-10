@props(['field', 'key', 'errors'])

<div {{ $attributes->class(['relative', 'md:col-span-2' => $field['width'] === 100, 'md:col-span-1' => $field['width'] === 50,]) }} wire:key="{{ $key }}">
    <label for="{{ $field['handle'] }}" class="field-label">
        {{ __($field['display']) }}
    </label>
    <select
        wire:model="form.{{ $field['handle'] }}"
        id="{{ $field['handle'] }}"
        @class(['select', 'has-error' => $errors->has('form.' . $field['handle'])])
        @if (array_key_exists('instructions', $field))
        aria-describedby="{{ $field['handle'] }}-explanation"
        @endif
    >
        <option selected>{{ __('Please select') }}</option>
        @if (array_key_exists('options', $field))
        @foreach ($field['options'] as $option)
        <option value="{{ $option['key'] }}">{{ __($option['value']) }}</option>
        @endforeach
        @endif
    </select>
    @if (array_key_exists('instructions', $field))
    <p id="{{ $field['handle'] }}-explanation" class="field-help">
        {{ __($field['instructions']) }}
    </p>
    @endif
    @if ($errors->has('form.' . $field['handle']))
    <p class="field-error">{{ implode(', ', $errors->get('form.' . $field['handle'])) }}</p>
    @endif
</div>
