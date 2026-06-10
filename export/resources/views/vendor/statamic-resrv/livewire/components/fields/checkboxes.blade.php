@props(['field', 'key', 'errors'])

<div {{ $attributes->class(['relative', 'md:col-span-2' => $field['width'] === 100, 'md:col-span-1' => $field['width'] === 50,]) }} wire:key="{{ $key }}">
    <label for="{{ $field['handle'] }}" class="field-label">
        {{ __($field['display']) }}
    </label>
    @foreach ($field['options'] as $key => $label)
    <div class="flex items-center mb-3">
        <input type="checkbox" wire:model="form.{{ $field['handle'] }}" id="{{ $key }}" value="{{ $key }}" class="w-4 h-4 rounded border-line accent-terracotta focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-terracotta">
        <label for="{{ $key }}" class="ms-2.5 text-[15px]">{{ $label }}</label>
    </div>
    @endforeach
    @if (array_key_exists('instructions', $field))
    <p id="{{ $field['handle'] }}-explanation" class="field-help">
        {{ __($field['instructions']) }}
    </p>
    @endif
    @if ($errors->has('form.' . $field['handle']))
    <p class="field-error">{{ implode(', ', $errors->get('form.' . $field['handle'])) }}</p>
    @endif
</div>
