@use(Carbon\Carbon)
{{-- Restyled to the design's checkout layout (checkout.html): progress strip, steps
     column + sticky order-summary card with hold timer. All wire:/Livewire structure
     (step entanglement, child components, gateway logic) is stock — markup only.
     Alpine-only additions: a step change scrolls (instantly under reduced motion) and
     moves focus to the new step's heading ([data-step-heading], numbered by step and
     waited for until the morph renders it, so the outgoing heading is never the
     target; picking or changing a payment method does the same); a polite sr-only
     region announces the running total; the hold timer stays silent except for one
     status at a minute left and one at 00:00. --}}
@php($totals = $this->calculateReservationTotals())
<div
    x-ref="checkout"
    x-data="{
        step: $wire.entangle('step'),
        focusStepHeading(selector, frames = 60) {
            const heading = this.$refs.checkout.querySelector(selector);
            if (heading && heading.getClientRects().length) {
                heading.focus({ preventScroll: true });
            } else if (frames > 0) {
                requestAnimationFrame(() => this.focusStepHeading(selector, frames - 1));
            }
        },
    }"
    x-init="$watch('step', () => {
        $refs.checkout.scrollIntoView({ behavior: matchMedia('(prefers-reduced-motion: reduce)').matches ? 'auto' : 'smooth' });
        $nextTick(() => focusStepHeading(`[data-step-heading^='${step}']`));
    })"
    x-on:gateway-selected.window="focusStepHeading(`[data-step-heading='3']`)"
    x-on:resrv-change-payment-method.window="focusStepHeading(`[data-step-heading='3-gateway']`)"
>

    {{-- progress --}}
    <div class="border-b border-line pb-5 mb-8 lg:mb-10">
        <x-resrv::checkout-steps :$step :$enableExtrasStep />
    </div>

    <p class="sr-only" aria-live="polite" aria-atomic="true">{{ trans('statamic-resrv::frontend.total') }}: {{ config('resrv-config.currency_symbol') }}{{ $totals->get('total')->format() }}</p>

    @if ($errors->has('reservation'))
    <div class="rounded-lg border border-error/30 bg-error/5 p-4 mb-8" role="alert">
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
                <h1 class="t-h1 mb-2 focus:outline-none" tabindex="-1" data-step-heading="1">{{ trans('statamic-resrv::frontend.extrasAndOptions') }}</h1>
                <p class="text-base leading-relaxed text-muted mb-8">Choose your options, then add any extras you&rsquo;d like.</p>
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
                    <x-resrv::checkout-step-button wire:click="handleFirstStep()" class="w-full sm:w-auto sm:min-w-65">
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
        <aside class="order-1 lg:order-2 lg:sticky lg:top-25 min-w-0">
            <div class="bg-white rounded-lg shadow-card overflow-hidden">
                {{-- The reserved entry's own image (rooms: hero_image; spa treatments and
                     the restaurant: image). Nothing at all when it has none. --}}
                @php($image = $this->entry->augmentedValue($this->entry->get('hero_image') ? 'hero_image' : 'image')->value())
                @php($image = $image instanceof \Illuminate\Support\Collection ? $image->first() : $image)
                @if ($image)
                <div class="img-slot has-img aspect-video">
                    <img src="{{ $image->manipulate(['w' => 800]) }}" alt="">
                </div>
                @endif
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
                        :$totals
                        :key="'pt-'.$enabledExtras->extras->pluck('id')->join('-').$enabledOptions->options->pluck('id')->join('-')"
                    />
                </div>
                @php($holdSeconds = max(0, Carbon::parse($this->reservation->created_at)->addMinutes((int) config('resrv-config.minutes_to_hold', 0))->timestamp - now()->timestamp))
                @if (config('resrv-config.minutes_to_hold', 0) > 0)
                <div
                    class="bg-shell px-5 lg:px-6 py-4 flex items-center gap-3 border-t border-line"
                    x-data="{ s: {{ $holdSeconds }}, holdNote: '' }"
                    x-init="setInterval(() => {
                        if (s > 0) {
                            s--;
                            if (s === 60) holdNote = @js(__('1 minute left to complete your booking at this rate.'));
                            if (s === 0) holdNote = @js(__('The hold on this rate has ended. You may need to start your booking again.'));
                        }
                    }, 1000)"
                >
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" class="size-5 text-warning shrink-0" aria-hidden="true"><circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/></svg>
                    <p class="text-sm text-muted">We'll hold this rate for <span class="font-semibold text-ink tabular-nums" x-text="`${String(Math.floor(s/60)).padStart(2,'0')}:${String(s%60).padStart(2,'0')}`"></span></p>
                    <p class="sr-only" role="status" x-text="holdNote"></p>
                </div>
                @endif
            </div>
            {{-- "Secure booking" already sits in the checkout header. --}}
            <p class="hidden lg:block mt-4 text-sm text-muted">No account needed.</p>
        </aside>
    </div>
</div>
