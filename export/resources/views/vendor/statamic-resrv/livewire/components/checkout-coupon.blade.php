{{-- Restyled to the design's "Have a code?" coupon affordance (checkout.html step 2).
     The coupon state and the addCoupon/removeCoupon wiring are stock. Keyboard layer
     (Alpine only): Escape or Cancel closes the open field and returns focus to "Add
     coupon"; an applied code takes focus so it is announced; removing it returns focus
     to "Add coupon". The field has a real label and its error is linked and announced.
     focusWhenShown() waits for the x-show to reveal its target: the coupon events can
     fire while Livewire is still settling a commit, before x-show has run. --}}
<div
    x-data="{
        coupon: $wire.coupon,
        open: false,
        applied: false,
        errors: false,
        toggle() {
            this.open = ! this.open;
        },
        close() {
            this.open = false;
            this.focusWhenShown('couponTrigger');
        },
        focusWhenShown(ref, frames = 30) {
            const el = this.$refs[ref];
            if (el && el.getClientRects().length) {
                el.focus();
            } else if (frames > 0) {
                requestAnimationFrame(() => this.focusWhenShown(ref, frames - 1));
            }
        },
    }"
    x-init="
        $wire.coupon !== null ? applied = true : applied = false;
        $watch('open', (value) => {
            if (open === false && ! applied) {
                coupon = null;
            }
        });
    "
    x-on:coupon-applied.window="applied = true; open = false; focusWhenShown('couponApplied')"
    x-on:coupon-removed.window="applied = false; coupon = null; focusWhenShown('couponTrigger')"
    class="my-4"
    x-ref="coupon"
>
    <button type="button" x-ref="couponTrigger" class="flex items-center gap-2 min-h-6 rounded-sm text-sm font-semibold text-teal hover:text-teal-hover transition-colors cursor-pointer" x-cloak x-show="! open && ! coupon && $wire.selectedGateway === ''" x-on:click="toggle">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" class="size-4" aria-hidden="true"><path d="M20 12a2 2 0 0 1 2-2V6a2 2 0 0 0-2-2H4a2 2 0 0 0-2 2v4a2 2 0 0 1 0 4v4a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2v-4a2 2 0 0 1-2-2Z"/></svg>
        {{ trans('statamic-resrv::frontend.addCoupon') }}
    </button>
    <div
        x-cloak
        x-show="open && $wire.selectedGateway === ''"
        x-on:click.outside="toggle"
        x-on:keydown.escape.prevent.stop="close()"
        x-trap.noreturn="open"
        class="flex items-center gap-2"
    >
        <div class="relative flex-1 min-w-0">
            <label for="resrv-coupon" class="sr-only">{{ trans('statamic-resrv::frontend.addCoupon') }}</label>
            <input
                x-model="coupon"
                type="text"
                id="resrv-coupon"
                autocomplete="off"
                autocapitalize="characters"
                spellcheck="false"
                placeholder="{{ trans('statamic-resrv::frontend.addCoupon') }}"
                x-on:keyup.enter="$wire.addCoupon(coupon)"
                @if ($errors->has('coupon'))
                aria-invalid="true"
                aria-describedby="resrv-coupon-error"
                @endif
                @class(['input pr-20', 'has-error' => $errors->has('coupon')])
            >
            <button
                type="button"
                class="absolute right-1.5 top-1/2 -translate-y-1/2 h-9 px-3 rounded-md cursor-pointer text-sm font-semibold text-teal hover:text-teal-hover hover:bg-shell transition-colors"
                x-show="applied === false && coupon !== null"
                wire:click.debounce="addCoupon(coupon)"
            >
                {{ trans('statamic-resrv::frontend.apply') }}
            </button>
            <div class="absolute left-0 top-0 w-full h-full bg-white/50" wire:loading></div>
        </div>
        <button
            type="button"
            x-on:click="close()"
            class="h-12 px-2 -mr-2 rounded-md cursor-pointer text-sm font-semibold text-muted hover:text-ink transition-colors"
        >
            {{ __('Cancel') }}
        </button>
    </div>
    <div x-cloak x-show="coupon !== null && applied === true" class="relative">
        <div x-ref="couponApplied" tabindex="-1" class="flex items-center bg-shell border border-line w-full ps-3.5 pe-12 py-2.5 rounded-md">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" class="size-4 text-success shrink-0" aria-hidden="true"><path d="M20 12a2 2 0 0 1 2-2V6a2 2 0 0 0-2-2H4a2 2 0 0 0-2 2v4a2 2 0 0 1 0 4v4a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2v-4a2 2 0 0 1-2-2Z"/></svg>
            <span class="sr-only">{{ __('Coupon applied:') }}</span>
            <span x-text="coupon" class="ml-2.5 text-sm font-semibold text-success"></span>
        </div>
        <button
            type="button"
            class="absolute right-1.5 top-1/2 -translate-y-1/2 grid place-items-center size-9 rounded-md cursor-pointer text-muted hover:text-ink hover:bg-sand transition-colors"
            wire:click.debounce="removeCoupon()"
            x-show="step === 1"
            x-bind:aria-label="@js(__('Remove coupon')) + ' ' + coupon"
        >
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-4" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
            </svg>
        </button>
    </div>
    @if ($errors->has('coupon'))
    <div id="resrv-coupon-error" class="field-error mt-2" role="alert">
        <span class="block">{{ $errors->first('coupon') }}</span>
    </div>
    @endif
</div>
