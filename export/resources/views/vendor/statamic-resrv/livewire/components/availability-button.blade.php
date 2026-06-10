@props(['label' => null])

{{-- Restyled to the design's primary button. wire:click / wire:loading unchanged. --}}
<div class="{{ $attributes->get('class') }}">
    <button
        class="inline-flex w-full h-full min-h-12 items-center justify-center gap-2 px-7 rounded-lg border-[1.5px] border-transparent bg-terracotta text-white text-[15px] font-semibold leading-none whitespace-nowrap transition hover:bg-terracotta-dark focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-terracotta disabled:opacity-50 cursor-pointer"
        wire:click="submit()"
        wire:loading.attr="disabled"
    >
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" class="w-[18px] h-[18px]"><circle cx="11" cy="11" r="7"/><path d="m21 21-4.3-4.3"/></svg>
        <span>{{ $label ?? trans('statamic-resrv::frontend.search') }}</span>
    </button>
</div>
