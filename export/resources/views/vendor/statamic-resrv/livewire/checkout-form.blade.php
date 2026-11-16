{{-- Restyled to the design's "Your details" step (checkout.html step 2): heading +
     white card hosting the dynamic checkout-form fields. Field components and
     submit wiring are stock. The h1 takes focus when the step opens (see checkout),
     and a submit that fails validation moves focus to the first invalid field: the
     marker below is re-keyed on every render with errors, so its x-init runs each time. --}}
<div>
    @if ($errors->any())
    <span hidden wire:key="resrv-form-errors-{{ uniqid() }}" x-init="$nextTick(() => $el.parentElement.querySelector('[aria-invalid=true]')?.focus())"></span>
    @endif
    <h1 class="t-h1 mb-2 focus:outline-none" tabindex="-1" data-step-heading="2">{{ trans('statamic-resrv::frontend.personalDetails') }}</h1>
    <p class="text-base leading-relaxed text-muted mb-8">{{ trans('statamic-resrv::frontend.personalDetailsDescription') }}</p>

    <div class="bg-white rounded-lg shadow-card p-5 lg:p-6">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
            @foreach ($this->checkoutForm as $field)
                <x-dynamic-component
                    :component="'resrv::fields.' . $field['type']"
                    :$field
                    :$errors
                    :key="$field['handle']"
                />
            @endforeach
        </div>
    </div>

    <div class="flex justify-end mt-10">
        <x-resrv::checkout-step-button wire:click="submit()" class="w-full sm:w-auto sm:min-w-65">
            @if ($this->reservation->payment->isZero())
                {{ trans('statamic-resrv::frontend.confirmReservation') }}
            @else
                {{ trans('statamic-resrv::frontend.continueToPayment') }}
            @endif
        </x-resrv::checkout-step-button>
    </div>
    @if ($affiliateCanSkipPayment)
    <div class="flex justify-end mt-3">
        <x-resrv::checkout-step-button wire:click="confirmWithoutPayment()" :$affiliateCanSkipPayment class="w-full sm:w-auto sm:min-w-65">
            {{ trans('statamic-resrv::frontend.completeWithoutPayment') }}
        </x-resrv::checkout-step-button>
    </div>
    @endif
</div>
