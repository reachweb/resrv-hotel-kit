@props(['maxAdults' => 5, 'maxChildren' => 3, 'maxInfants' => 2, 'variant' => 'card'])

{{-- Restyled to the design's guests popover (step-btn steppers, "2 guests" summary).
     Same Alpine `guests` component contract as stock — binds to data.customer.* on the
     parent AvailabilitySearch. Two stock script bugs fixed: the third watcher watched
     `children` but wrote `infants`, and the children max-guard checked `childs`.
     Variants: card (hero widget) · compact (sticky bar) · panel (booking sidebar). --}}
<div x-data="guests" class="relative h-full {{ $attributes->get('class') }}">
    @if ($variant === 'compact')
        <button
            type="button"
            x-on:click.stop="toggleGuestsPopup"
            x-ref="guestsButton"
            class="w-full h-full flex items-center gap-2.5 px-4 py-2.5 hover:bg-shell text-left transition-colors cursor-pointer"
        >
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" class="w-[18px] h-[18px] text-sage shrink-0"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/></svg>
            <span>
                <span class="block text-xs font-semibold uppercase tracking-[0.12em] text-muted leading-tight">Guests</span>
                <span class="block text-sm font-medium whitespace-nowrap" x-text="guestsText">2 guests</span>
            </span>
        </button>
    @elseif ($variant === 'panel')
        <button
            type="button"
            x-on:click.stop="toggleGuestsPopup"
            x-ref="guestsButton"
            class="w-full h-full border border-line rounded-md p-3 text-left hover:border-ink transition-colors cursor-pointer"
        >
            <span class="block text-xs font-semibold uppercase tracking-[0.12em] text-muted">Guests</span>
            <span class="block text-sm font-medium whitespace-nowrap mt-0.5" x-text="guestsText">2 guests</span>
        </button>
    @else
        <button
            type="button"
            x-on:click.stop="toggleGuestsPopup"
            x-ref="guestsButton"
            class="sw-field w-full h-full min-h-[62px] cursor-pointer"
        >
            <span class="text-xs font-semibold uppercase tracking-[0.12em] text-muted">Guests</span>
            <span class="text-[15px] font-medium whitespace-nowrap" x-text="guestsText">2 guests</span>
        </button>
    @endif

    <div
        x-show="guestsPopup"
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0 translate-y-1"
        x-transition:enter-end="opacity-100 translate-y-0"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100 translate-y-0"
        x-transition:leave-end="opacity-0 translate-y-1"
        class="absolute top-full z-50 mt-2 w-[300px]"
        role="menu"
        aria-orientation="vertical"
        aria-labelledby="menu-button"
        tabindex="-1"
        x-on:click.outside="guestsPopup = false"
        x-cloak
        x-ref="guestsPopup"
        x-anchor.offset.10="$refs.guestsButton"
    >
        <div class="bg-white rounded-lg shadow-raised border border-line p-4" role="none">
            <div class="flex items-center justify-between py-2.5">
                <div class="pr-8">
                    <span class="block font-medium text-[15px]">{{ trans('Adults') }}</span>
                    <span class="block text-sm text-muted">{{ trans('Ages 13+') }}</span>
                </div>
                <div class="flex items-center gap-3">
                    <button type="button" class="step-btn" x-bind:class="{'is-disabled': adults <= 1}" x-on:click="decrement('adults')" aria-label="Fewer adults">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6"><path d="M5 12h14"/></svg>
                    </button>
                    <span class="w-5 text-center tabular-nums font-medium" x-text="adults"></span>
                    <button type="button" class="step-btn" x-bind:class="{'is-disabled': adults >= {{ $maxAdults }}}" x-on:click="increment('adults')" aria-label="More adults">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6"><path d="M12 5v14M5 12h14"/></svg>
                    </button>
                </div>
            </div>
            <div class="h-px bg-line my-1"></div>
            <div class="flex items-center justify-between py-2.5">
                <div class="pr-8">
                    <span class="block font-medium text-[15px]">{{ trans('Children') }}</span>
                    <span class="block text-sm text-muted">{{ trans('Ages 2–12') }}</span>
                </div>
                <div class="flex items-center gap-3">
                    <button type="button" class="step-btn" x-bind:class="{'is-disabled': children <= 0}" x-on:click="decrement('children')" aria-label="Fewer children">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6"><path d="M5 12h14"/></svg>
                    </button>
                    <span class="w-5 text-center tabular-nums font-medium" x-text="children"></span>
                    <button type="button" class="step-btn" x-bind:class="{'is-disabled': children >= {{ $maxChildren }}}" x-on:click="increment('children')" aria-label="More children">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6"><path d="M12 5v14M5 12h14"/></svg>
                    </button>
                </div>
            </div>
            <div class="h-px bg-line my-1"></div>
            <div class="flex items-center justify-between py-2.5">
                <div class="pr-8">
                    <span class="block font-medium text-[15px]">{{ trans('Infants') }}</span>
                    <span class="block text-sm text-muted">{{ trans('Under 2') }}</span>
                </div>
                <div class="flex items-center gap-3">
                    <button type="button" class="step-btn" x-bind:class="{'is-disabled': infants <= 0}" x-on:click="decrement('infants')" aria-label="Fewer infants">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6"><path d="M5 12h14"/></svg>
                    </button>
                    <span class="w-5 text-center tabular-nums font-medium" x-text="infants"></span>
                    <button type="button" class="step-btn" x-bind:class="{'is-disabled': infants >= {{ $maxInfants }}}" x-on:click="increment('infants')" aria-label="More infants">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6"><path d="M12 5v14M5 12h14"/></svg>
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>


@script
<script>
Alpine.data('guests', () => ({
    adults: $wire.data.customer.adults,
    children: $wire.data.customer.children,
    infants: $wire.data.customer.infants,
    guestsPopup: false,
    maxima: { adults: {{ $maxAdults }}, children: {{ $maxChildren }}, infants: {{ $maxInfants }} },

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
