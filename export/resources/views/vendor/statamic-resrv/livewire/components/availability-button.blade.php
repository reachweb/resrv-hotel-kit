@props(['label' => null])

{{-- Restyled to the kit's primary button (<x-resrv::kit-cta>). wire:click / wire:loading unchanged.
     Kit hardening: with no dates the click is stopped in the capture phase (before
     wire:click sees it), so the server never answers with three "field is required"
     errors that stock hides while the dates are empty and that then linger, stale,
     once dates are picked. The date field shows a prompt and opens the calendar instead. --}}
<div
    class="{{ $attributes->get('class') }}"
    x-data="{
        requireDates(event) {
            const dates = this.$wire.data.dates || {};
            if (dates.date_start && dates.date_end) return;
            event.stopPropagation();
            event.preventDefault();
            this.$dispatch('availability-dates-required', { id: this.$wire.$id });
        },
    }"
    x-on:click.capture="requireDates($event)"
>
    <x-resrv::kit-cta full wire:click="submit()" wire:loading.attr="disabled">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" class="size-4.5" aria-hidden="true"><circle cx="11" cy="11" r="7"/><path d="m21 21-4.3-4.3"/></svg>
        <span>{{ $label ?? trans('statamic-resrv::frontend.search') }}</span>
    </x-resrv::kit-cta>
</div>
