@use(Carbon\Carbon)
{{-- Restyled to the design's checkout layout (checkout.html): progress strip, steps
     column + sticky order-summary card with hold timer. All wire:/Livewire structure
     (step entanglement, child components, gateway logic) is stock — markup only. --}}
<div x-ref="checkout" x-data="{ step: $wire.entangle('step') }" x-init="$watch('step', () => $refs.checkout.scrollIntoView({ behavior: 'smooth' }))">

    {{-- progress --}}
    <div class="border-b border-line pb-5 mb-8 lg:mb-10">
        <x-resrv::checkout-steps :$step :$enableExtrasStep />
    </div>

    @if ($errors->has('reservation'))
    <div class="rounded-lg border border-error/30 bg-error/5 p-4 mb-8">
        <p class="font-medium text-error">{{ trans('statamic-resrv::frontend.somethingWentWrong') }}</p>
        <div class="text-sm text-muted mt-1">
            @foreach ($errors->get('reservation') as $index => $error)
                <div wire:key="{{ $index }}">{{ $error }}</div>
            @endforeach
        </div>
    </div>
    @endif

    <div class="grid lg:grid-cols-[1fr_400px] gap-8 lg:gap-12 items-start">

        {{-- STEPS --}}
        <div class="order-2 lg:order-1 min-w-0">
            @if ($step === 1)
                <h1 class="t-h1 mb-2">{{ trans('statamic-resrv::frontend.extrasAndOptions') }}</h1>
                <p class="text-base leading-relaxed text-muted mb-8">Make your stay yours &mdash; everything here is optional.</p>
                <livewire:options
                    :reservation="$this->reservation"
                    :errors="$errors->get('options')"
                />
                <livewire:extras
                    :reservation="$this->reservation"
                    :$coupon
                    :errors="$errors->get('extras')"
                />
                <div class="flex justify-end mt-10">
                    <x-resrv::checkout-step-button wire:click="handleFirstStep()" class="w-full sm:w-auto sm:min-w-[260px]">
                        {{ trans('statamic-resrv::frontend.continueToPersonalDetails') }}
                    </x-resrv::checkout-step-button>
                </div>
            @endif
            @if ($step === 2)
                <livewire:checkout-form :reservation="$this->reservation" :affiliateCanSkipPayment="$this->affiliateCanSkipPayment()" />
            @endif
            @if ($step === 3)
                @if (count($availableGateways) > 1 && empty($selectedGateway))
                    <x-resrv::checkout-gateway-picker :gateways="$availableGateways" />
                @else
                    <livewire:checkout-payment
                        :clientSecret="$clientSecret"
                        :publicKey="$publicKey"
                        :amount="$this->reservation->fresh()->totalToCharge()"
                        :paymentView="$paymentView"
                    />
                @endif
            @endif
        </div>

        {{-- ORDER SUMMARY --}}
        <aside class="order-1 lg:order-2 lg:sticky lg:top-[100px] min-w-0">
            <div class="bg-white rounded-lg shadow-card overflow-hidden">
                <div class="img-slot" style="aspect-ratio: 16/9;">
                    <span class="slot-label">Booking image · 16:9</span>
                </div>
                <div class="p-5 lg:p-6">
                    <x-resrv::checkout-reservation-details
                        :entry="$this->entry"
                        :reservation="$this->reservation"
                    />
                    @if ($this->enableCoupon && $step < 3)
                    <x-resrv::checkout-coupon />
                    @endif
                    <x-resrv::checkout-payment-table
                        :$enabledExtras
                        :$enabledOptions
                        :totals="$this->calculateReservationTotals()"
                        :key="'pt-'.$enabledExtras->extras->pluck('id')->join('-').$enabledOptions->options->pluck('id')->join('-')"
                    />
                </div>
                @php($holdSeconds = max(0, Carbon::parse($this->reservation->created_at)->addMinutes((int) config('resrv-config.minutes_to_hold', 0))->timestamp - now()->timestamp))
                @if (config('resrv-config.minutes_to_hold', 0) > 0)
                <div
                    class="bg-shell px-5 lg:px-6 py-4 flex items-center gap-3 border-t border-line"
                    x-data="{ s: {{ $holdSeconds }} }"
                    x-init="setInterval(() => { if (s > 0) s-- }, 1000)"
                >
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" class="w-5 h-5 text-warning shrink-0"><circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/></svg>
                    <p class="text-sm text-muted">We'll hold this rate for <span class="font-semibold text-ink tabular-nums" x-text="`${String(Math.floor(s/60)).padStart(2,'0')}:${String(s%60).padStart(2,'0')}`"></span></p>
                </div>
                @endif
            </div>
            <div class="hidden lg:flex items-center gap-2.5 mt-4 text-sm text-muted">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" class="w-4 h-4 text-success shrink-0"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10Z"/><path d="m9 12 2 2 4-4"/></svg>
                Secure booking &middot; no account needed.
            </div>
        </aside>
    </div>
</div>
