{{-- Restyled to the design's offline-payment panel (checkout.html step 3): icon
     circle, copy, amount strip, confirm + change-method actions. The offlinePayment
     Alpine component and all wire: calls are stock. Confirm is the kit's shared
     <x-resrv::kit-cta>, the h1 takes focus when the step opens (see checkout), the
     panel heading is an h2, and errors are announced (role="alert") as plain text. --}}
<div>
    <div x-data="offlinePayment">
        <h1 class="t-h1 mb-2 focus:outline-none" tabindex="-1" data-step-heading="3">{{ trans('statamic-resrv::frontend.payment') }}</h1>
        <p class="text-base leading-relaxed text-muted mb-8">{{ trans('statamic-resrv::frontend.offlinePaymentDescription') }}</p>

        <div class="bg-white rounded-lg shadow-card p-5 lg:p-6">
            <div class="flex items-start gap-4">
                <div class="size-11 rounded-full bg-sand grid place-items-center text-teal shrink-0">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" class="size-5" aria-hidden="true"><rect x="2" y="5" width="20" height="14" rx="2"/><path d="M2 10h20"/></svg>
                </div>
                <div class="flex-1 min-w-0">
                    <h2 class="text-xl font-semibold mb-1">Pay at the property</h2>
                    <p class="text-base leading-relaxed text-muted">We'll hold your reservation with no payment today &mdash; settle on arrival, or by bank transfer beforehand if you prefer.</p>
                    <div class="bg-shell rounded-md p-4 mt-4 flex items-center justify-between gap-3">
                        <span class="text-sm text-muted">{{ trans('statamic-resrv::frontend.offlinePaymentAmount') }}</span>
                        <span class="font-display text-2xl tabular-nums">{{ config('resrv-config.currency_symbol') }}{{ number_format((float) $amount, 2) }}</span>
                    </div>
                </div>
            </div>
        </div>

        <div class="flex flex-col sm:flex-row-reverse sm:items-center sm:justify-between gap-3 mt-8">
            <x-resrv::kit-cta
                x-on:click="confirmReservation()"
                x-bind:disabled="loading"
            >
                <span class="py-0.5" x-cloak x-transition x-show="loading === true">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="animate-spin size-5" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0 3.181 3.183a8.25 8.25 0 0 0 13.803-3.7M4.031 9.865a8.25 8.25 0 0 1 13.803-3.7l3.181 3.182m0-4.991v4.99" />
                    </svg>
                    <span class="sr-only">{{ trans('statamic-resrv::frontend.paymentHeadingProcessing') }}</span>
                </span>
                <span x-transition x-show="loading === false">
                    {{ trans('statamic-resrv::frontend.confirmReservation') }}
                </span>
            </x-resrv::kit-cta>
            <button
                type="button"
                class="inline-flex items-center justify-center gap-2 h-12 px-5 rounded-lg text-ui font-semibold leading-none text-muted hover:text-ink transition cursor-pointer disabled:cursor-not-allowed disabled:opacity-70"
                x-on:click="$dispatch('resrv-change-payment-method'); $wire.$parent.resetPaymentState()"
                x-bind:disabled="loading"
                x-show="$wire.$parent.availableGateways.length > 1"
            >
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="size-4" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5 8.25 12l7.5-7.5" />
                </svg>
                {{ trans('statamic-resrv::frontend.changePaymentMethod') }}
            </button>
        </div>

        <div class="flex flex-wrap gap-x-8 gap-y-3 mt-8 text-sm text-muted">
            <span class="inline-flex items-center gap-2">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" class="size-4 text-success" aria-hidden="true"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10Z"/><path d="m9 12 2 2 4-4"/></svg>
                Secure booking
            </span>
            <span class="inline-flex items-center gap-2">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" class="size-4 text-success" aria-hidden="true"><path d="M20 6 9 17l-5-5"/></svg>
                No card required today
            </span>
        </div>

        @if ($errors->has('reservation'))
        <div class="rounded-lg border border-error/30 bg-error/5 p-4 mt-6" role="alert">
            <div class="text-sm text-error">
                @foreach ($errors->get('reservation') as $error)
                    <div>{{ $error }}</div>
                @endforeach
            </div>
        </div>
        @endif
        <p x-show="errors" x-cloak x-transition class="mt-6 text-error" role="alert">
            <span x-text="errors"></span>
        </p>
    </div>
</div>

@script
<script>
Alpine.data('offlinePayment', () => ({
    loading: false,
    errors: false,

    async confirmReservation() {
        this.loading = true;
        this.errors = false;

        try {
            await $wire.confirmPayment();
        } catch (e) {
            this.errors = e.message || 'An unexpected error occurred. Please try again.';
        } finally {
            this.loading = false;
        }
    }
}));
</script>
@endscript
