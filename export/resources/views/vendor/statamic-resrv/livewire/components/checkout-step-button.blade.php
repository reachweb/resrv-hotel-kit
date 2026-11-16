@props(['affiliateCanSkipPayment' => false])

{{-- Restyled to the design's primary/secondary buttons. wire:click passthrough and
     the loading overlay are stock. The primary variant is the kit's shared
     <x-resrv::kit-cta>; the affiliate "complete without payment" secondary keeps its
     teal outline at the same size. --}}
<div class="{{ $attributes->get('class') }}">
    @if ($affiliateCanSkipPayment)
    <button
        type="button"
        class="relative w-full inline-flex items-center justify-center h-12 px-6 rounded-lg border-[1.5px] border-teal bg-transparent text-teal text-ui font-semibold leading-none whitespace-nowrap transition cursor-pointer overflow-hidden hover:bg-teal hover:text-white disabled:cursor-not-allowed disabled:opacity-70"
        {{ $attributes->whereStartsWith('wire:click') }}
    >
        <span class="absolute inset-0 bg-white/20" wire:loading.delay.long>
            <span class="flex items-center justify-center size-full">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="animate-spin size-5" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0 3.181 3.183a8.25 8.25 0 0 0 13.803-3.7M4.031 9.865a8.25 8.25 0 0 1 13.803-3.7l3.181 3.182m0-4.991v4.99" />
                </svg>
            </span>
        </span>
        <span>{{ $slot }}</span>
    </button>
    @else
    <x-resrv::kit-cta full class="overflow-hidden" {{ $attributes->whereStartsWith('wire:click') }}>
        <span class="absolute inset-0 bg-white/20" wire:loading.delay.long>
            <span class="flex items-center justify-center size-full">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="animate-spin size-5" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0 3.181 3.183a8.25 8.25 0 0 0 13.803-3.7M4.031 9.865a8.25 8.25 0 0 1 13.803-3.7l3.181 3.182m0-4.991v4.99" />
                </svg>
            </span>
        </span>
        <span>{{ $slot }}</span>
    </x-resrv::kit-cta>
    @endif
</div>
