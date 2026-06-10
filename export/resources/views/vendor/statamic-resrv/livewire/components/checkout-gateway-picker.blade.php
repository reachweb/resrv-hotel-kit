@props(['gateways'])

{{-- Restyled to the design's payment-method cards (.pay-method / .pm-radio).
     The gateway-selected dispatch and loading states are stock. --}}
<div>
    <h1 class="t-h1 mb-2">{{ trans('statamic-resrv::frontend.payment') }}</h1>
    <p class="text-base leading-relaxed text-muted mb-8">{{ trans('statamic-resrv::frontend.selectPaymentMethod') }}</p>
    <div class="grid sm:grid-cols-2 gap-4">
        @foreach ($gateways as $gateway)
            <button
                type="button"
                wire:click="$dispatch('gateway-selected', { gateway: @js($gateway['name']) })"
                wire:loading.attr="disabled"
                class="pay-method disabled:opacity-50 disabled:cursor-wait"
            >
                <span class="flex items-center gap-3">
                    <span class="pm-radio"></span>
                    <span class="font-medium">{{ $gateway['label'] }}</span>
                    <svg wire:loading xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" class="w-4 h-4 animate-spin text-teal ml-auto">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                    </svg>
                </span>
                @if (! empty($gateway['surcharge']))
                    <span class="block text-sm text-muted mt-2 pl-7">
                        <span class="text-warning">+{{ $gateway['surcharge']['type'] === 'percent'
                            ? $gateway['surcharge']['amount'] . '%'
                            : config('resrv-config.currency_symbol') . number_format($gateway['surcharge']['amount'], 2)
                        }} {{ trans('statamic-resrv::frontend.surcharge') }}</span>
                    </span>
                @endif
            </button>
        @endforeach
    </div>
</div>
