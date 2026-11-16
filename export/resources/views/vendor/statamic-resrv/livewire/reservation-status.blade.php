@use(Reach\StatamicResrv\Enums\ReservationStatus)
{{-- Restyled to the kit's booking surfaces: brand form controls and primary button for
     the lookup, and checkout-complete's summary card for a found reservation (rooms get
     check-in/out tiles with the booking-global times, single-date products a date tile).
     Every wire: directive, the lookup/cancel flow and the conditions are stock —
     markup and classes only. Lookup fields carry aria-required, and an error sets
     aria-invalid, is linked through aria-describedby and is announced; the lookup
     button is the kit's shared <x-resrv::kit-cta>. --}}
<div class="w-full">
    @if (! config('resrv-config.enable_reservation_status_page'))
        {{-- Feature is opt-in: render nothing at all when disabled. --}}
    @elseif ($this->reservation === null)
        <h1 class="t-h1 mb-2">{{ trans('statamic-resrv::frontend.findYourReservation') }}</h1>
        <p class="text-base leading-relaxed text-muted mb-8">
            {{ trans('statamic-resrv::frontend.findYourReservationDescription') }}
        </p>
        @if ($linkFailed)
        <div class="rounded-lg border border-warning/30 bg-warning/5 p-4 mb-6" role="status">
            <p class="text-sm text-ink">{{ trans('statamic-resrv::frontend.reservationLinkFailed') }}</p>
        </div>
        @endif
        @if ($errors->has('lookup'))
        <div class="rounded-lg border border-error/30 bg-error/5 p-4 mb-6" role="alert">
            <p class="font-medium text-error">{{ trans('statamic-resrv::frontend.somethingWentWrong') }}</p>
            <p class="text-sm text-muted mt-1">{{ $errors->first('lookup') }}</p>
        </div>
        @endif
        <form wire:submit="lookup" class="bg-white rounded-lg shadow-card p-6 lg:p-8 flex flex-col gap-5">
            <div>
                <label for="resrv-status-email" class="field-label">
                    {{ trans('statamic-resrv::frontend.emailAddress') }}
                </label>
                <input
                    wire:model="email"
                    type="email"
                    id="resrv-status-email"
                    name="email"
                    autocomplete="email"
                    aria-required="true"
                    @if ($errors->has('email'))
                    aria-invalid="true"
                    aria-describedby="resrv-status-email-error"
                    @endif
                    @class(['input', 'has-error' => $errors->has('email')])
                />
                @if ($errors->has('email'))
                <p id="resrv-status-email-error" class="field-error" role="alert">{{ implode(', ', $errors->get('email')) }}</p>
                @endif
            </div>
            <div>
                <label for="resrv-status-reference" class="field-label">
                    {{ trans('statamic-resrv::frontend.bookingReference') }}
                </label>
                <input
                    wire:model="reference"
                    type="text"
                    id="resrv-status-reference"
                    name="reference"
                    autocomplete="off"
                    autocapitalize="characters"
                    spellcheck="false"
                    aria-required="true"
                    @if ($errors->has('reference'))
                    aria-invalid="true"
                    aria-describedby="resrv-status-reference-error"
                    @endif
                    @class(['input', 'has-error' => $errors->has('reference')])
                />
                @if ($errors->has('reference'))
                <p id="resrv-status-reference-error" class="field-error" role="alert">{{ implode(', ', $errors->get('reference')) }}</p>
                @endif
            </div>
            <x-resrv::kit-cta type="submit" full class="mt-1 overflow-hidden">
                <span class="absolute inset-0 bg-white/20" wire:loading.delay.long wire:target="lookup">
                    <span class="flex items-center justify-center size-full">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="animate-spin size-5" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0 3.181 3.183a8.25 8.25 0 0 0 13.803-3.7M4.031 9.865a8.25 8.25 0 0 1 13.803-3.7l3.181 3.182m0-4.991v4.99" />
                        </svg>
                    </span>
                </span>
                <span>{{ trans('statamic-resrv::frontend.findReservation') }}</span>
            </x-resrv::kit-cta>
        </form>
    @else
        @php($reservation = $this->reservation)
        @php($entry = $reservation->entry)
        @php($isStay = Statamic\Facades\Entry::find($reservation->item_id)?->collectionHandle() === 'rooms')
        @php($booking = Statamic\Facades\GlobalSet::findByHandle('booking')?->inDefaultSite())
        @php($currency = config('resrv-config.currency_symbol'))
        <div class="flex flex-wrap items-center justify-between gap-3 mb-8">
            <h1 class="t-h1">{{ trans('statamic-resrv::frontend.reservationStatus') }}</h1>
            <span @class([
                'inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-sm font-semibold leading-none',
                'bg-success/10 text-success' => $reservation->isLive(),
                'bg-sand text-muted' => in_array($reservation->status, [ReservationStatus::REFUNDED->value, ReservationStatus::CANCELLED->value, ReservationStatus::COMPLETED->value], true),
            ])>
                {{ $this->statusLabel }}
            </span>
        </div>

        @if ($cancelled)
        <div class="rounded-lg border border-success/30 bg-success/5 p-4 mb-6" role="status">
            <p class="text-sm text-ink">{{ $this->cancellationSuccessMessage }}</p>
        </div>
        @endif

        <div class="bg-white rounded-lg shadow-card p-6 lg:p-8">
            <div class="flex flex-wrap items-start justify-between gap-4 mb-6">
                <div class="min-w-0">
                    <h2 class="t-h3 break-words">
                        {{ $entry['id'] ? $entry['title'] : trans('statamic-resrv::frontend.reservationItemUnavailable') }}
                    </h2>
                    @if (! $reservation->isParent() && $reservation->rate_id)
                    <p class="text-sm text-muted mt-0.5">{{ $reservation->getRateLabel() }}</p>
                    @endif
                </div>
                <div class="sm:text-right">
                    <p class="text-sm text-muted mb-1">{{ trans('statamic-resrv::frontend.bookingReference') }}</p>
                    <p class="font-display text-2xl tabular-nums">{{ $reservation->reference }}</p>
                </div>
            </div>

            @if ($reservation->isParent())
                <div class="space-y-3 mb-6">
                    @foreach ($reservation->childs as $child)
                    <div class="bg-shell rounded-md p-4" wire:key="child-{{ $child->id }}">
                        <p class="text-sm text-muted">
                            {{ trans('statamic-resrv::frontend.reservationPeriod') }}
                            @if ($child->rate_id)
                            &middot; {{ $child->getRateLabel() }}
                            @endif
                        </p>
                        <p class="font-medium">
                            {{ $child->date_start->format('D j M Y') }}
                            {{ trans('statamic-resrv::frontend.to') }}
                            {{ $child->date_end->format('D j M Y') }}
                            @if ($child->quantity > 1)
                            &middot; x{{ $child->quantity }}
                            @endif
                        </p>
                    </div>
                    @endforeach
                </div>
            @elseif ($isStay)
                <div class="grid sm:grid-cols-2 gap-4 mb-6">
                    <div class="bg-shell rounded-md p-4">
                        <p class="text-sm text-muted">Check-in</p>
                        <p class="font-medium">{{ $reservation->date_start->format('D j M Y') }}</p>
                        @if ($booking?->get('check_in_time'))
                        <p class="text-sm text-muted">from {{ $booking->get('check_in_time') }}</p>
                        @endif
                    </div>
                    <div class="bg-shell rounded-md p-4">
                        <p class="text-sm text-muted">Check-out</p>
                        <p class="font-medium">{{ $reservation->date_end->format('D j M Y') }}</p>
                        @if ($booking?->get('check_out_time'))
                        <p class="text-sm text-muted">by {{ $booking->get('check_out_time') }}</p>
                        @endif
                    </div>
                </div>
            @else
                <div class="bg-shell rounded-md p-4 mb-6">
                    <p class="text-sm text-muted">{{ trans('statamic-resrv::frontend.date') }}</p>
                    <p class="font-medium">{{ $reservation->date_start->format('D j M Y') }}</p>
                </div>
            @endif

            <div class="text-sm">
                @if (! $reservation->isParent() && $reservation->quantity > 1)
                <div class="flex justify-between gap-4 py-3 border-t border-line">
                    {{-- Single-date products count people (the kit's quantityLabel override: "Guests"). --}}
                    <span class="text-muted">{{ trans($isStay ? 'statamic-resrv::frontend.quantity' : 'statamic-resrv::frontend.quantityLabel') }}</span>
                    <span class="font-medium">x{{ $reservation->quantity }}</span>
                </div>
                @endif
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
                @php($cancellationLabel = $reservation->isLive() ? $reservation->cancellationPolicyLabel() : null)
                @if ($cancellationLabel)
                <div class="flex justify-between gap-4 py-3 border-t border-line">
                    <span class="text-muted">{{ trans('statamic-resrv::frontend.cancellationPolicy') }}</span>
                    <span class="font-medium text-right">{{ $cancellationLabel }}</span>
                </div>
                @endif
            </div>

            <div class="flex items-baseline justify-between gap-4 pt-4 mt-1 border-t border-line">
                <span class="font-medium">{{ trans('statamic-resrv::frontend.total') }}</span>
                <span class="font-display text-2xl tabular-nums">{{ $currency }}{{ $reservation->total->format() }}</span>
            </div>
            <div class="flex justify-between gap-4 pt-2 text-sm">
                <span class="text-muted">{{ trans('statamic-resrv::frontend.amountPaid') }}</span>
                {{-- A receipt the admin recorded counts as paid; when receipt is unknown (offline checkout, unresolved intent) only what was verifiably collected online does. --}}
                <span class="font-medium tabular-nums">{{ $currency }}{{ ($reservation->amountReceived() ?? $reservation->amountPaidOnline())->format() }}</span>
            </div>
        </div>

        @if ($errors->has('cancellation'))
        <div class="rounded-lg border border-error/30 bg-error/5 p-4 mt-6" role="alert">
            <p class="font-medium text-error">{{ trans('statamic-resrv::frontend.somethingWentWrong') }}</p>
            <p class="text-sm text-muted mt-1">{{ $errors->first('cancellation') }}</p>
        </div>
        @endif

        @if (! $cancelled && config('resrv-config.enable_customer_cancellations'))
            @if ($reservation->canCancelWithRefund())
            <div class="mt-8">
                <p class="text-base leading-relaxed text-muted mb-4">
                    {{ trans($reservation->hasGatewayPayment() ? 'statamic-resrv::frontend.cancelReservationDescription' : 'statamic-resrv::frontend.cancelReservationNoPaymentDescription') }}
                </p>
                <button
                    type="button"
                    wire:click="cancel"
                    wire:confirm="{{ trans('statamic-resrv::frontend.cancelReservationConfirmation') }}"
                    wire:loading.attr="disabled"
                    class="inline-flex items-center justify-center w-full sm:w-auto h-12 px-8 rounded-lg border-[1.5px] border-error bg-transparent text-error text-ui font-semibold leading-none whitespace-nowrap transition hover:bg-error hover:text-white focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-error disabled:opacity-70 cursor-pointer"
                >
                    <span wire:loading.remove wire:target="cancel">{{ trans('statamic-resrv::frontend.cancelReservation') }}</span>
                    <span wire:loading wire:target="cancel">{{ trans('statamic-resrv::frontend.cancelReservation') }}&hellip;</span>
                </button>
            </div>
            @elseif ($reservation->canCancelWithoutRefund())
            <div class="mt-8">
                <div class="rounded-lg border border-error/30 bg-error/5 p-4 mb-4">
                    <p class="text-sm text-ink mb-2">
                        {{ trans($reservation->freeCancellationExpired() ? 'statamic-resrv::frontend.cancelReservationNoRefundDescription' : 'statamic-resrv::frontend.cancelReservationNonRefundableDescription') }}
                    </p>
                    <p class="text-sm font-semibold text-error">
                        {{ trans('statamic-resrv::frontend.cancelReservationNoRefundWarning') }}
                    </p>
                </div>
                <button
                    type="button"
                    wire:click="cancel"
                    wire:confirm="{{ trans('statamic-resrv::frontend.cancelReservationNoRefundConfirmation') }}"
                    wire:loading.attr="disabled"
                    class="inline-flex items-center justify-center w-full sm:w-auto h-12 px-8 rounded-lg border-[1.5px] border-error bg-transparent text-error text-ui font-semibold leading-none whitespace-nowrap transition hover:bg-error hover:text-white focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-error disabled:opacity-70 cursor-pointer"
                >
                    <span wire:loading.remove wire:target="cancel">{{ trans('statamic-resrv::frontend.cancelReservationNoRefund') }}</span>
                    <span wire:loading wire:target="cancel">{{ trans('statamic-resrv::frontend.cancelReservationNoRefund') }}&hellip;</span>
                </button>
            </div>
            @elseif ($reservation->isLive())
            {{-- Live but not self-serviceable (offline/manual gateway, partner booking, or a
                 no-longer-configured gateway): explain instead of leaving a silent dead end. --}}
            <div class="rounded-lg border border-line bg-sand/60 p-5 mt-8 text-sm text-muted">
                @if ($reservation->freeCancellationExpired())
                <p class="mb-1">{{ trans('statamic-resrv::frontend.freeCancellationExpired') }}</p>
                @endif
                <p>{{ trans('statamic-resrv::frontend.cancellationNotAllowed') }}</p>
            </div>
            @endif
        @endif

        <div class="mt-8">
            <button
                type="button"
                wire:click="startOver"
                class="inline-flex items-center gap-2 text-sm font-medium text-teal hover:text-terracotta-dark transition-colors cursor-pointer"
            >
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-5" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 15 3 9m0 0 6-6M3 9h12a6 6 0 0 1 0 12h-3" />
                </svg>
                {{ trans('statamic-resrv::frontend.lookUpAnotherReservation') }}
            </button>
        </div>
    @endif
</div>
