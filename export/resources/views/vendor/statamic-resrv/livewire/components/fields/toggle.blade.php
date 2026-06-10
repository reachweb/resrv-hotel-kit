@props(['field', 'key', 'errors'])

<div {{ $attributes->class(['relative', 'md:col-span-2' => $field['width'] === 100, 'md:col-span-1' => $field['width'] === 50,]) }} wire:key="{{ $key }}">
    <label class="inline-flex items-center cursor-pointer">
        <input type="checkbox" class="sr-only peer" wire:model="form.{{ $field['handle'] }}">
        <div
            class="relative flex-shrink-0 w-11 h-6 bg-line peer-focus:outline-none peer-focus:ring-2 peer-focus:ring-terracotta/40
            rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white
            after:content-[''] after:absolute after:top-[2px] after:start-[2px] after:bg-white after:border-line
            after:border after:rounded-full after:w-5 after:h-5 after:transition-all peer-checked:bg-teal"
        >
        </div>
        <span class="ms-3 text-[15px] font-medium">{{ __($field['display']) }}</span>
    </label>
    @if (array_key_exists('instructions', $field))
    <p id="{{ $field['handle'] }}-explanation" class="field-help">
        {{ __($field['instructions']) }}
    </p>
    @endif
    @if ($errors->has('form.' . $field['handle']))
    <p class="field-error">{{ implode(', ', $errors->get('form.' . $field['handle'])) }}</p>
    @endif
</div>
