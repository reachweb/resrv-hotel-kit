@props(['affiliateCanSkipPayment' => false])

{{-- Restyled to the design's primary/secondary buttons. wire:click passthrough and
     the loading overlay are stock. --}}
<div class="{{ $attributes->get('class') }}">
    <button
        type="button"
        @class([
            'relative w-full inline-flex items-center justify-center h-12 px-8 rounded-lg border-[1.5px] text-[15px] font-semibold
            leading-none whitespace-nowrap transition cursor-pointer disabled:opacity-70 overflow-hidden
            focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-terracotta',
            'bg-terracotta border-transparent text-white hover:bg-terracotta-dark' => ! $affiliateCanSkipPayment,
            'bg-transparent border-teal text-teal hover:bg-teal hover:text-white' => $affiliateCanSkipPayment
        ])
        {{ $attributes->whereStartsWith('wire:click') }}
    >
        <span class="absolute left-0 right-0 top-0 w-full h-full bg-white/20" wire:loading.delay.long>
            <span class="flex items-center justify-center w-full h-full">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="animate-spin w-5 h-5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0 3.181 3.183a8.25 8.25 0 0 0 13.803-3.7M4.031 9.865a8.25 8.25 0 0 1 13.803-3.7l3.181 3.182m0-4.991v4.99" />
                </svg>
            </span>
        </span>
        <span>{{ $slot }}</span>
    </button>
</div>
