@props(['maxAdults' => null, 'maxChildren' => null, 'maxInfants' => null, 'variant' => 'card'])

@php
    // Picker limits come from the Booking global (CP: Globals → Booking), falling back to
    // the stock maxima. An explicit prop on the tag still wins over the global.
    $bookingGlobal = \Statamic\Facades\GlobalSet::findByHandle('booking')?->inCurrentSite();
    $maxAdults ??= (int) ($bookingGlobal?->get('max_adults') ?? 5);
    $maxChildren ??= (int) ($bookingGlobal?->get('max_children') ?? 3);
    $maxInfants ??= (int) ($bookingGlobal?->get('max_infants') ?? 2);
@endphp

{{-- Restyled to the design's guests popover (step-btn steppers, "2 guests" summary).
     Same Alpine `guests` component contract as stock — binds to data.customer.* on the
     parent AvailabilitySearch. Two stock script bugs fixed: the third watcher watched
     `children` but wrote `infants`, and the children max-guard checked `childs`.
     Variants: card (hero widget) · compact (sticky bar) · panel (booking sidebar).

     Kit hardening (keep through 3-way merges on upgrade):
     · `guests($wire, maxima)`: each instance binds to its own search component (stock's
       @script `$wire` belongs to whichever search registered last — see availability-dates).
     · The popover is a dialog: opening moves focus into it and Tab stays inside (it is
       teleported to <body>, so it isn't next in tab order); Escape or Done closes it and
       returns focus to the field; counts are announced as they change. --}}
<div
    x-data="guests($wire, @js(['adults' => $maxAdults, 'children' => $maxChildren, 'infants' => $maxInfants]))"
    class="relative h-full {{ $attributes->get('class') }}"
>
    @if ($variant === 'compact')
        <button
            type="button"
            x-on:click.stop="toggleGuestsPopup"
            x-ref="guestsButton"
            aria-haspopup="dialog"
            x-bind:aria-expanded="guestsPopup.toString()"
            class="w-full h-full flex items-center gap-2.5 px-4 py-2.5 hover:bg-shell text-left transition-colors cursor-pointer focus-visible:outline-2 focus-visible:-outline-offset-2 focus-visible:outline-terracotta-dark"
        >
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" class="hidden sm:block size-4.5 text-sage shrink-0" aria-hidden="true"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/></svg>
            <span class="min-w-0">
                <span class="block text-xs font-semibold uppercase tracking-widest text-muted leading-tight">Guests</span>
                <span class="block text-sm font-medium truncate" x-text="guestsText">2 guests</span>
            </span>
        </button>
    @elseif ($variant === 'panel')
        <button
            type="button"
            x-on:click.stop="toggleGuestsPopup"
            x-ref="guestsButton"
            aria-haspopup="dialog"
            x-bind:aria-expanded="guestsPopup.toString()"
            class="w-full h-full min-w-0 border border-line-strong rounded-md p-3 text-left hover:border-ink transition-colors cursor-pointer focus-visible:outline-2 focus-visible:-outline-offset-1 focus-visible:outline-terracotta-dark"
        >
            <span class="block text-xs font-semibold uppercase tracking-widest text-muted">Guests</span>
            <span class="block text-sm font-medium truncate mt-0.5" x-text="guestsText">2 guests</span>
        </button>
    @else
        <button
            type="button"
            x-on:click.stop="toggleGuestsPopup"
            x-ref="guestsButton"
            aria-haspopup="dialog"
            x-bind:aria-expanded="guestsPopup.toString()"
            class="sw-field w-full h-full min-h-15.5 cursor-pointer focus-visible:outline-2 focus-visible:-outline-offset-2 focus-visible:outline-terracotta-dark"
        >
            <span class="text-xs font-semibold uppercase tracking-widest text-muted">Guests</span>
            <span class="text-ui font-medium whitespace-nowrap" x-text="guestsText">2 guests</span>
        </button>
    @endif

    {{-- Teleported to <body> so an ancestor's overflow-hidden can't clip it — the compact
         sticky-bar variant wraps its fields in a rounded `overflow-hidden` pill, which
         otherwise hides this popup entirely. x-anchor keeps it pinned to the button. --}}
    <template x-teleport="body">
    <div
        x-show="guestsPopup"
        x-transition:enter="transition ease-out duration-200 motion-reduce:transition-none"
        x-transition:enter-start="opacity-0 translate-y-1"
        x-transition:enter-end="opacity-100 translate-y-0"
        x-transition:leave="transition ease-in duration-150 motion-reduce:transition-none"
        x-transition:leave-start="opacity-100 translate-y-0"
        x-transition:leave-end="opacity-0 translate-y-1"
        class="absolute top-full z-50 mt-2 w-75 max-w-[calc(100vw-2rem)]"
        role="dialog"
        aria-label="{{ trans('Guests') }}"
        x-trap.noreturn="guestsPopup"
        x-on:keydown.escape.prevent.stop="closeGuestsPopup()"
        x-on:click.outside="guestsPopup = false"
        x-cloak
        x-ref="guestsPopup"
        x-anchor.offset.10="$refs.guestsButton"
    >
        <div class="bg-white rounded-lg shadow-raised border border-line p-4">
            <div class="flex items-center justify-between py-2.5">
                <div class="pr-8">
                    <span class="block font-medium text-ui">{{ trans('Adults') }}</span>
                    <span class="block text-sm text-muted">{{ trans('Ages 13+') }}</span>
                </div>
                <div class="flex items-center gap-3">
                    <button type="button" class="step-btn" x-bind:class="{'is-disabled': adults <= 1}" x-bind:aria-disabled="(adults <= 1).toString()" x-on:click="decrement('adults')" aria-label="{{ trans('Fewer adults') }}">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" aria-hidden="true"><path d="M5 12h14"/></svg>
                    </button>
                    <span class="w-5 text-center tabular-nums font-medium" aria-live="polite" aria-atomic="true"><span x-text="adults"></span><span class="sr-only" x-text="adults == 1 ? @js(' '.trans('adult')) : @js(' '.trans('adults'))"> {{ trans('adults') }}</span></span>
                    <button type="button" class="step-btn" x-bind:class="{'is-disabled': adults >= maxima.adults}" x-bind:aria-disabled="(adults >= maxima.adults).toString()" x-on:click="increment('adults')" aria-label="{{ trans('More adults') }}">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" aria-hidden="true"><path d="M12 5v14M5 12h14"/></svg>
                    </button>
                </div>
            </div>
            <div class="h-px bg-line my-1"></div>
            <div class="flex items-center justify-between py-2.5">
                <div class="pr-8">
                    <span class="block font-medium text-ui">{{ trans('Children') }}</span>
                    <span class="block text-sm text-muted">{{ trans('Ages 2–12') }}</span>
                </div>
                <div class="flex items-center gap-3">
                    <button type="button" class="step-btn" x-bind:class="{'is-disabled': children <= 0}" x-bind:aria-disabled="(children <= 0).toString()" x-on:click="decrement('children')" aria-label="{{ trans('Fewer children') }}">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" aria-hidden="true"><path d="M5 12h14"/></svg>
                    </button>
                    <span class="w-5 text-center tabular-nums font-medium" aria-live="polite" aria-atomic="true"><span x-text="children"></span><span class="sr-only" x-text="children == 1 ? @js(' '.trans('child')) : @js(' '.trans('children'))"> {{ trans('children') }}</span></span>
                    <button type="button" class="step-btn" x-bind:class="{'is-disabled': children >= maxima.children}" x-bind:aria-disabled="(children >= maxima.children).toString()" x-on:click="increment('children')" aria-label="{{ trans('More children') }}">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" aria-hidden="true"><path d="M12 5v14M5 12h14"/></svg>
                    </button>
                </div>
            </div>
            <div class="h-px bg-line my-1"></div>
            <div class="flex items-center justify-between py-2.5">
                <div class="pr-8">
                    <span class="block font-medium text-ui">{{ trans('Infants') }}</span>
                    <span class="block text-sm text-muted">{{ trans('Under 2') }}</span>
                </div>
                <div class="flex items-center gap-3">
                    <button type="button" class="step-btn" x-bind:class="{'is-disabled': infants <= 0}" x-bind:aria-disabled="(infants <= 0).toString()" x-on:click="decrement('infants')" aria-label="{{ trans('Fewer infants') }}">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" aria-hidden="true"><path d="M5 12h14"/></svg>
                    </button>
                    <span class="w-5 text-center tabular-nums font-medium" aria-live="polite" aria-atomic="true"><span x-text="infants"></span><span class="sr-only" x-text="infants == 1 ? @js(' '.trans('infant')) : @js(' '.trans('infants'))"> {{ trans('infants') }}</span></span>
                    <button type="button" class="step-btn" x-bind:class="{'is-disabled': infants >= maxima.infants}" x-bind:aria-disabled="(infants >= maxima.infants).toString()" x-on:click="increment('infants')" aria-label="{{ trans('More infants') }}">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" aria-hidden="true"><path d="M12 5v14M5 12h14"/></svg>
                    </button>
                </div>
            </div>
            <div class="flex justify-end pt-2 mt-1 border-t border-line">
                <button type="button" x-on:click="closeGuestsPopup()" class="px-3 py-2 -mr-2 rounded-md text-sm font-semibold text-terracotta-dark hover:text-terracotta-deep hover:bg-shell transition-colors cursor-pointer">{{ trans('Done') }}</button>
            </div>
        </div>
    </div>
    </template>
</div>


@script
<script>
// `$wire` and `maxima` come from each instance's x-data: this factory is shared by every
// search on the page, so it must not use the script-scoped `$wire` or Blade values.
Alpine.data('guests', ($wire, maxima) => ({
    adults: $wire.data.customer.adults,
    children: $wire.data.customer.children,
    infants: $wire.data.customer.infants,
    guestsPopup: false,
    maxima: maxima,

    init() {
        if (this.adults === undefined) {
            this.adults = 2;
            $wire.set('data.customer.adults', 2);
        }
        if (this.children === undefined) {
            this.children = 0;
            $wire.set('data.customer.children', 0);
        }
        if (this.infants === undefined) {
            this.infants = 0;
            $wire.set('data.customer.infants', 0);
        }
        this.$watch('adults', value => {
            $wire.set('data.customer.adults', value);
        });

        this.$watch('children', value => {
            $wire.set('data.customer.children', value);
        });

        this.$watch('infants', value => {
            $wire.set('data.customer.infants', value);
        });
    },

    toggleGuestsPopup() {
        this.guestsPopup = ! this.guestsPopup
    },
    // Escape / Done: close and hand focus back to the field (a click outside just closes,
    // leaving focus where the guest clicked). After the tick, once x-trap has let go —
    // focusing earlier is pulled straight back into the closing popover.
    closeGuestsPopup() {
        this.guestsPopup = false
        this.$nextTick(() => this.$refs.guestsButton.focus())
    },
    guestsText() {
        const guests = this.adults + this.children;
        let text = `${guests} ${guests === 1 ? 'guest' : 'guests'}`;
        if (this.infants > 0) {
            text += `, ${this.infants} ${this.infants === 1 ? 'infant' : 'infants'}`;
        }
        return text;
    },
    increment(key) {
        if (this[key] >= this.maxima[key]) {
            return ''
        }
        this[key] = this[key] + 1
    },
    decrement(key) {
        const min = key === 'adults' ? 1 : 0;
        if (this[key] > min) {
            this[key] = this[key] - 1
        }
    },
}));
</script>
@endscript
