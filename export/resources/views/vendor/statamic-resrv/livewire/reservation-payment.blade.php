{{-- Restyled to the kit's booking surfaces: the state heading is the page's h1 (as on
     checkout, whose restyled checkout-payment view this component mounts after Pay),
     state notices use the brand tints, and the reservation sits in checkout-complete's
     summary card. A broken link gets the neutral "unavailable" heading so the page is
     never headless. Every wire: directive, state and condition is stock — markup and
     classes only. The Pay button is the kit's shared <x-resrv::kit-cta>. --}}
<div class="w-full">
    @if ($linkFailed || $this->reservation === null)
        <h1 class="t-h1 mb-6">{{ trans('statamic-resrv::frontend.paymentHeadingUnavailable') }}</h1>
        <div class="flex flex-col gap-2 rounded-lg border border-warning/30 bg-warning/5 p-5" role="status">
            <p class="text-sm text-ink">{{ trans('statamic-resrv::frontend.paymentLinkFailed') }}</p>
            @include('statamic-resrv::livewire.components.partials.business-contact')
        </div>
    @else
        @php($reservation = $this->reservation)
        @php($entry = $reservation->entry)
        @php($state = $this->state)
        @php($payable = in_array($state, ['awaiting', 'instructions'], true))
        @php($isStay = Statamic\Facades\Entry::find($reservation->item_id)?->collectionHandle() === 'rooms')
        @php($currency = config('resrv-config.currency_symbol'))
        <h1 class="t-h1 mb-6">{{ $this->heading }}</h1>

        @if ($state === 'paid')
            <div class="rounded-lg border border-success/30 bg-success/5 p-5 mb-6" role="status">
                <p class="text-sm text-ink">{{ trans('statamic-resrv::frontend.paymentAlreadyCompleted') }}</p>
            </div>
        @elseif ($state === 'processing')
            <div class="rounded-lg border border-sage bg-sage-light/30 p-5 mb-6" role="status">
                <p class="text-sm text-ink">{{ trans('statamic-resrv::frontend.paymentProcessing') }}</p>
            </div>
        @elseif ($state === 'deadline_passed')
            <div class="flex flex-col gap-2 rounded-lg border border-warning/30 bg-warning/5 p-5 mb-6" role="status">
                <p class="text-sm text-ink">{{ trans('statamic-resrv::frontend.paymentLinkExpired') }}</p>
                @include('statamic-resrv::livewire.components.partials.business-contact')
            </div>
        @elseif ($state === 'arrival_passed')
            <div class="flex flex-col gap-2 rounded-lg border border-warning/30 bg-warning/5 p-5 mb-6" role="status">
                <p class="text-sm text-ink">{{ trans('statamic-resrv::frontend.paymentArrivalPassed') }}</p>
                @include('statamic-resrv::livewire.components.partials.business-contact')
            </div>
        @elseif ($state === 'unavailable')
            <div class="flex flex-col gap-2 rounded-lg border border-line bg-sand/60 p-5 mb-6" role="status">
                <p class="text-sm text-ink">{{ trans('statamic-resrv::frontend.paymentUnavailable') }}</p>
                @include('statamic-resrv::livewire.components.partials.business-contact')
            </div>
        @endif

        <div class="bg-white rounded-lg shadow-card p-6 lg:p-8">
            @if ($payable)
            <div class="pb-6 mb-6 border-b border-line">
                <p class="text-sm text-muted mb-1">{{ trans('statamic-resrv::frontend.amountToPay') }}</p>
                <p class="font-display text-4xl tabular-nums">{{ $currency }}{{ $this->amountDue }}</p>
                @if ($this->paymentDueLine)
                <p class="mt-2 text-sm font-medium text-ink">{{ $this->paymentDueLine }}</p>
                @endif
            </div>
            @endif

            <div class="flex flex-wrap items-start justify-between gap-4 mb-6">
                <div class="min-w-0">
                    <h2 class="t-h3 break-words">
                        {{ $entry['id'] ? $entry['title'] : trans('statamic-resrv::frontend.reservationItemUnavailable') }}
                    </h2>
                    @if ($reservation->rate_id && $reservation->rate)
                    <p class="text-sm text-muted mt-0.5">{{ $reservation->rate->title }}</p>
                    @endif
                </div>
                <div class="sm:text-right">
                    <p class="text-sm text-muted mb-1">{{ trans('statamic-resrv::frontend.bookingReference') }}</p>
                    <p class="font-display text-2xl tabular-nums">{{ $reservation->reference }}</p>
                </div>
            </div>

            @if ($isStay)
                <div class="grid sm:grid-cols-2 gap-4">
                    <div class="bg-shell rounded-md p-4">
                        <p class="text-sm text-muted">Check-in</p>
                        <p class="font-medium">{{ $reservation->date_start->format('D j M Y') }}</p>
                    </div>
                    <div class="bg-shell rounded-md p-4">
                        <p class="text-sm text-muted">Check-out</p>
                        <p class="font-medium">{{ $reservation->date_end->format('D j M Y') }}@if ($reservation->quantity > 1) &middot; x{{ $reservation->quantity }}@endif</p>
                    </div>
                </div>
            @else
                <div class="bg-shell rounded-md p-4">
                    <p class="text-sm text-muted">{{ trans('statamic-resrv::frontend.date') }}</p>
                    <p class="font-medium">{{ $reservation->date_start->format('D j M Y') }}@if ($reservation->quantity > 1) &middot; x{{ $reservation->quantity }}@endif</p>
                </div>
            @endif

            @if ($payable)
                <div class="text-sm mt-6">
                    @if ($reservation->extras->isNotEmpty())
                    <div class="flex justify-between gap-4 py-3 border-t border-line">
                        <span class="text-muted">{{ trans('statamic-resrv::frontend.extras') }}</span>
                        <span class="text-right">
                            @foreach ($reservation->extras as $extra)
                            <span class="block font-medium" wire:key="extra-{{ $extra->id }}">{{ $extra->name }} x{{ $extra->pivot->quantity }}</span>
                            @endforeach
                        </span>
                    </div>
                    @endif
                    @if ($reservation->options->isNotEmpty())
                    <div class="flex justify-between gap-4 py-3 border-t border-line">
                        <span class="text-muted">{{ trans('statamic-resrv::frontend.options') }}</span>
                        <span class="text-right">
                            @foreach ($reservation->options as $option)
                            <span class="block font-medium" wire:key="option-{{ $option->id }}">{{ $option->name }}: {{ $option->values->firstWhere('id', $option->pivot->value)?->name }}</span>
                            @endforeach
                        </span>
                    </div>
                    @endif
                    {{-- Total whenever the lead amount is not simply the total: a partial request or a surcharge. --}}
                    @if (! $reservation->payment->equals($reservation->total) || ! $reservation->payment_surcharge->isZero())
                    <div class="flex justify-between gap-4 py-3 border-t border-line">
                        <span class="text-muted">{{ trans('statamic-resrv::frontend.total') }}</span>
                        <span class="font-medium tabular-nums">{{ $currency }}{{ $reservation->total->format() }}</span>
                    </div>
                    @endif
                    @if (! $reservation->payment_surcharge->isZero())
                    <div class="flex justify-between gap-4 py-3 border-t border-line">
                        <span class="text-muted">{{ trans('statamic-resrv::frontend.paymentSurcharge') }}</span>
                        <span class="font-medium tabular-nums">{{ $currency }}{{ $reservation->payment_surcharge->format() }}</span>
                    </div>
                    @endif
                    @if ($this->balanceDue !== null)
                    <div class="flex justify-between gap-4 py-3 border-t border-line">
                        <span>
                            <span class="block text-muted">{{ trans('statamic-resrv::frontend.remainingBalance') }}</span>
                            <span class="block text-xs text-muted">{{ trans('statamic-resrv::frontend.remainingBalanceNote') }}</span>
                        </span>
                        <span class="font-medium tabular-nums">{{ $currency }}{{ $this->balanceDue }}</span>
                    </div>
                    @endif
                </div>
            @endif
        </div>

        @if ($state === 'instructions')
            <div class="flex flex-col gap-3 rounded-lg border border-line bg-sand/60 p-6 mt-6">
                @if ($this->gatewayLabel)
                <p class="font-semibold text-ink">{{ trans('statamic-resrv::frontend.paymentMethodIs', ['method' => $this->gatewayLabel]) }}</p>
                @endif
                @if ($this->offlineInstructions)
                <p class="text-ink break-words">{!! nl2br(e($this->offlineInstructions)) !!}</p>
                <p class="text-sm text-muted">{{ trans('statamic-resrv::frontend.paymentOfflineInstructions', ['reference' => $reservation->reference]) }}</p>
                @else
                <p class="text-sm text-muted">{{ trans('statamic-resrv::frontend.paymentOfflineNoInstructions', ['reference' => $reservation->reference]) }}</p>
                @include('statamic-resrv::livewire.components.partials.business-contact')
                @endif
            </div>
        @elseif ($state === 'awaiting')
            @if ($paymentRetryAt)
            <div class="rounded-lg border border-warning/30 bg-warning/5 p-4 mt-6" role="status">
                <p class="text-sm text-ink">{{ trans('statamic-resrv::frontend.paymentAttemptStillOpen', ['date' => $paymentRetryAt]) }}</p>
            </div>
            @endif
            @if ($paymentError)
            <div class="rounded-lg border border-error/30 bg-error/5 p-4 mt-6" role="alert">
                <p class="text-sm text-ink">{{ trans('statamic-resrv::frontend.paymentMountFailed') }}</p>
            </div>
            @endif
            <div class="mt-8">
                <x-resrv::kit-cta
                    full
                    wire:click="pay"
                    wire:loading.attr="disabled"
                    wire:target="pay"
                    class="overflow-hidden"
                >
                    <span class="absolute inset-0 bg-white/20" wire:loading wire:target="pay">
                        <span class="flex items-center justify-center size-full">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="animate-spin size-5" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0 3.181 3.183a8.25 8.25 0 0 0 13.803-3.7M4.031 9.865a8.25 8.25 0 0 1 13.803-3.7l3.181 3.182m0-4.991v4.99" />
                            </svg>
                            <span class="sr-only">{{ trans('statamic-resrv::frontend.paymentHeadingProcessing') }}</span>
                        </span>
                    </span>
                    <span wire:loading.class="invisible" wire:target="pay">
                        {{ trans('statamic-resrv::frontend.pay') }}
                        <span class="font-bold ml-1 whitespace-nowrap">{{ $currency }}{{ $this->amountDue }}</span>
                    </span>
                </x-resrv::kit-cta>
                @if ($this->gatewayLabel)
                <p class="mt-3 text-sm text-muted text-center">
                    {{ trans('statamic-resrv::frontend.paymentMethodIs', ['method' => $this->gatewayLabel]) }}
                </p>
                @endif
            </div>
        @endif
    @endif
</div>
