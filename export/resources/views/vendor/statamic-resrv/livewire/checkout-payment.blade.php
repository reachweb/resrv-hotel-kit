{{-- Restyled to the design's payment step (checkout.html step 3) — card panel
     hosting the Stripe Payment Element. The payment Alpine component and the whole
     Stripe flow are stock — markup/classes only. The Pay button is the kit's shared
     <x-resrv::kit-cta>, the h1 takes focus when the step opens (see checkout), and a
     Stripe error is announced (role="alert") as plain text. $amount is a float, so the
     button formats it through Price, as stock does since Resrv 6.5.3 (€779.30, not €779.3). --}}
<div>
    <div x-data="payment">
        <h1 class="t-h1 mb-2 focus:outline-none" tabindex="-1" data-step-heading="3">{{ trans('statamic-resrv::frontend.payment') }}</h1>
        <p class="text-base leading-relaxed text-muted mb-8">{{ trans('statamic-resrv::frontend.paymentDescription') }}</p>
        <div class="bg-white rounded-lg shadow-card p-5 lg:p-6">
            <form id="payment-form" x-on:submit.prevent>
                <div id="payment-element" x-ref="paymentElement">
                <!--Stripe.js injects the Payment Element here-->
                </div>
                <div class="mt-6">
                    <x-resrv::kit-cta
                        full
                        x-on:click="submitPayment()"
                        x-bind:disabled="loading"
                    >
                        <span class="py-0.5" x-cloak x-transition x-show="loading === true">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="animate-spin size-5" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0 3.181 3.183a8.25 8.25 0 0 0 13.803-3.7M4.031 9.865a8.25 8.25 0 0 1 13.803-3.7l3.181 3.182m0-4.991v4.99" />
                            </svg>
                            <span class="sr-only">{{ trans('statamic-resrv::frontend.paymentHeadingProcessing') }}</span>
                        </span>
                        <span x-transition x-show="loading === false">
                            {{ trans('statamic-resrv::frontend.pay') }}
                            <span class="font-bold">{{ config('resrv-config.currency_symbol') }}{{ \Reach\StatamicResrv\Facades\Price::create($amount)->format() }}</span>
                            {{ trans('statamic-resrv::frontend.toCompleteYourReservation') }}
                        </span>
                    </x-resrv::kit-cta>
                </div>
            </form>
            <p x-show="errors" x-cloak x-transition class="mt-6 text-error text-sm" role="alert">
                <span x-text="errors"></span>
            </p>
        </div>
    </div>
</div>

@script
<script>
Alpine.data('payment', () => ({
    client_secret: $wire.clientSecret,
    public_key: $wire.publicKey,
    checkout_completed_url: $wire.checkoutCompletedUrl,
    stripe: null,
    elements: null,
    loading: false,
    errors: false,

    init() {
        this.stripe = Stripe(this.public_key);
        this.elements = this.stripe.elements({ clientSecret: this.client_secret });

        const paymentElement = this.elements.create("payment", {
            layout: "accordion",
        });

        paymentElement.mount(this.$refs.paymentElement);
    },

    async submitPayment()
    {
        this.loading = true;

        const { error } = await this.stripe.confirmPayment({
            elements: this.elements,
            confirmParams: {
                return_url: this.checkout_completed_url,
            },
        })

        if (error.type === "card_error" || error.type === "validation_error") {
            this.errors = error.message;
        } else {
            this.errors.value = "An unexpected error occurred. Please contact us.";
        }

        this.loading = false;
    }

}));
</script>
@endscript
