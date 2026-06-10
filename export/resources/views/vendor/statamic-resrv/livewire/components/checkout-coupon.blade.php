{{-- Restyled to the design's "Have a code?" coupon affordance (checkout.html step 2).
     All Alpine state and addCoupon/removeCoupon wiring are stock — markup/classes only. --}}
<div
    x-data="{
        coupon: $wire.coupon,
        open: false,
        applied: false,
        errors: false,
        toggle() {
            this.open = ! this.open;
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
    x-on:coupon-applied.window="applied = true; open = false"
    x-on:coupon-removed.window="applied = false; coupon = null"
    class="my-4"
    x-ref="coupon"
>
    <button type="button" class="flex items-center gap-2 text-sm font-semibold text-teal hover:text-teal-hover transition-colors cursor-pointer" x-cloak x-show="! open && ! coupon && $wire.selectedGateway === ''" x-on:click="toggle">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" class="w-4 h-4"><path d="M20 12a2 2 0 0 1 2-2V6a2 2 0 0 0-2-2H4a2 2 0 0 0-2 2v4a2 2 0 0 1 0 4v4a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2v-4a2 2 0 0 1-2-2Z"/></svg>
        {{ trans('statamic-resrv::frontend.addCoupon') }}
    </button>
    <div x-cloak x-show="open && $wire.selectedGateway === ''" x-on:click.outside="toggle" class="relative" x-trap="open">
        <input
            x-model="coupon"
            type="text"
            placeholder="{{ trans('statamic-resrv::frontend.addCoupon') }}"
            x-on:keyup.enter="$wire.addCoupon(coupon)"
            class="input pr-16"
        >
        <button
            type="button"
            class="absolute right-4 top-1/2 -translate-y-1/2 cursor-pointer text-sm font-semibold text-teal hover:text-teal-hover transition-colors"
            x-show="applied === false && coupon !== null"
            wire:click.debounce="addCoupon(coupon)"
        >
            {{ trans('statamic-resrv::frontend.apply') }}
        </button>
        <div class="absolute left-0 top-0 w-full h-full bg-white/50" wire:loading></div>
    </div>
    <div x-cloak x-show="coupon !== null && applied === true" class="relative">
        <div class="flex items-center bg-shell border border-line w-full px-3.5 py-2.5 rounded-md">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" class="w-4 h-4 text-success shrink-0"><path d="M20 12a2 2 0 0 1 2-2V6a2 2 0 0 0-2-2H4a2 2 0 0 0-2 2v4a2 2 0 0 1 0 4v4a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2v-4a2 2 0 0 1-2-2Z"/></svg>
            <span x-html="coupon" class="ml-2.5 text-sm font-semibold text-success"></span>
            <button
                type="button"
                class="absolute right-3.5 top-1/2 -translate-y-1/2 cursor-pointer text-muted hover:text-ink transition-colors"
                wire:click.debounce="removeCoupon()"
                x-show="step === 1"
                aria-label="Remove coupon"
            >
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                </svg>
            </button>
        </div>
    </div>
    @if ($errors->has('coupon'))
    <div class="field-error mt-2">
        <span class="block">{{ $errors->first('coupon') }}</span>
    </div>
    @endif
</div>
