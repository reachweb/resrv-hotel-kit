@props(['field', 'key', 'errors'])

{{-- Restyled to the kit's brand toggle (teal); the wire:model binding is stock.
     Accessibility layer shared by every field view: required fields carry
     aria-required and optional ones (no `required` rule) say "(optional)" after the
     label; an error sets aria-invalid, is linked through aria-describedby and is
     announced (role="alert").
     The off track is Line Strong (3:1) and the track draws the focus ring for the
     sr-only checkbox; the label text names it. --}}
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
    <label class="inline-flex items-center cursor-pointer">
        <input
            type="checkbox"
            class="sr-only peer"
            wire:model="form.{{ $handle }}"
            id="{{ $handle }}"
            name="{{ $handle }}"
            @if ($hasError) aria-invalid="true" @endif
            @if ($describedBy) aria-describedby="{{ $describedBy }}" @endif
        >
        <span
            aria-hidden="true"
            class="relative block shrink-0 w-11 h-6 rounded-full bg-line-strong transition-[background-color] motion-reduce:transition-none peer-checked:bg-teal
            peer-focus-visible:outline-2 peer-focus-visible:outline-offset-2 peer-focus-visible:outline-terracotta-dark
            after:content-[''] after:absolute after:top-0.5 after:start-0.5 after:size-5 after:rounded-full
            after:bg-white after:border after:border-line after:transition-transform motion-reduce:after:transition-none
            peer-checked:after:translate-x-full peer-checked:after:border-white"
        ></span>
        <span class="ms-3 text-ui font-medium">{{ __($field['display']) }}@unless ($isRequired) <span class="font-normal text-muted">({{ __('optional') }})</span>@endunless</span>
    </label>
    @if (array_key_exists('instructions', $field))
    <p id="{{ $handle }}-explanation" class="field-help">
        {{ __($field['instructions']) }}
    </p>
    @endif
    @if ($hasError)
    <p id="{{ $handle }}-error" class="field-error" role="alert">{{ implode(', ', $errors->get('form.'.$handle)) }}</p>
    @endif
</div>
