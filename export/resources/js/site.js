/**
 * Resrv Hotel — front-end progressive enhancement.
 *
 * Small, one-off stateful UI (mobile sheet, accordion) stays inline in the
 * templates — the Alpine that Livewire 4 bundles. Centralised here: the
 * reveal-on-scroll observer, the `bookBar` phone booking bar, the `lightbox`
 * Alpine component shared by the room gallery and the page-builder gallery grid,
 * and the `listbox` picker with its `treatmentSearch` extension (the home hero's
 * Treatment tab: date, then treatment). The components
 * register on `alpine:init`, which is why site.js loads in <head>, before
 * {{ livewire:scripts }}.
 */

function initReveal(root = document) {
    const els = root.querySelectorAll('.reveal:not(.in)');
    if (!els.length) return;

    if (!('IntersectionObserver' in window)) {
        els.forEach((el) => el.classList.add('in'));
        return;
    }

    const io = new IntersectionObserver(
        (entries) => {
            entries.forEach((entry) => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('in');
                    io.unobserve(entry.target);
                }
            });
        },
        { threshold: 0.12, rootMargin: '0px 0px -8% 0px' }
    );

    els.forEach((el, i) => {
        el.style.transitionDelay = (Math.min(i % 6, 5) * 55) + 'ms';
        io.observe(el);
    });
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', () => initReveal());
} else {
    initReveal();
}

/**
 * Select-only listbox (WAI-ARIA listbox pattern) for pickers whose rows need more
 * than a native <select> can draw. Markup: a trigger `x-ref="button"` and a list
 * `x-ref="list"` (role=listbox, tabindex=-1) of `[role=option]` rows carrying
 * `data-value` + `data-label`. While open, focus sits on the list and
 * aria-activedescendant follows the highlighted row: arrows / Home / End move,
 * Enter / Space / click choose, Escape / click-outside close, a letter jumps to the
 * next row starting with it. `value` and `label` are readable by the surrounding
 * markup (e.g. a CTA's :href).
 */
function listbox(placeholder = '') {
    return {
        open: false,
        active: -1,
        value: '',
        label: placeholder,
        options() {
            return [...this.$refs.list.querySelectorAll('[role=option]')];
        },
        activeId() {
            return this.open && this.active >= 0 ? this.options()[this.active]?.id : null;
        },
        show(index = null) {
            const options = this.options();
            const selected = options.findIndex((option) => option.dataset.value === this.value);
            this.active = index === -1 ? options.length - 1 : (index ?? Math.max(selected, 0));
            this.open = true;
            this.$nextTick(() => {
                this.$refs.list.focus({ preventScroll: true });
                this.reveal();
            });
        },
        hide(refocus = true) {
            this.open = false;
            if (refocus) this.$refs.button.focus();
        },
        toggle() {
            this.open ? this.hide() : this.show();
        },
        move(step) {
            this.active = Math.min(this.options().length - 1, Math.max(0, this.active + step));
            this.reveal();
        },
        to(index) {
            this.active = index === -1 ? this.options().length - 1 : index;
            this.reveal();
        },
        /** Keep the highlighted row inside the list's own scroll area, never scrolling the page. */
        reveal() {
            const list = this.$refs.list;
            const option = this.options()[this.active];
            if (!option) return;
            if (option.offsetTop < list.scrollTop) {
                list.scrollTop = option.offsetTop;
            } else if (option.offsetTop + option.offsetHeight > list.scrollTop + list.clientHeight) {
                list.scrollTop = option.offsetTop + option.offsetHeight - list.clientHeight;
            }
        },
        choose(index = this.active) {
            const option = this.options()[index];
            if (!option) return;
            this.value = option.dataset.value;
            this.label = option.dataset.label;
            this.hide();
        },
        typeahead(key) {
            if (key.length !== 1 || key === ' ') return;
            const options = this.options();
            const letter = key.toLowerCase();
            for (let offset = 1; offset <= options.length; offset++) {
                const index = (this.active + offset) % options.length;
                if (options[index].dataset.label.toLowerCase().startsWith(letter)) {
                    this.to(index);
                    return;
                }
            }
        },
    };
}

document.addEventListener('alpine:init', () => {
    /**
     * Mobile booking bar (partials/book-bar). Visible only while none of the page's
     * `[data-hides-book-bar]` elements (the booking UI it would duplicate) is on
     * screen. With nothing marked it simply shows.
     */
    Alpine.data('bookBar', () => ({
        visible: false,
        init() {
            const blockers = document.querySelectorAll('[data-hides-book-bar]');
            if (!blockers.length || !('IntersectionObserver' in window)) {
                this.visible = true;
                return;
            }

            const onScreen = new Set();
            this.observer = new IntersectionObserver((entries) => {
                entries.forEach((entry) => {
                    entry.isIntersecting ? onScreen.add(entry.target) : onScreen.delete(entry.target);
                });
                this.visible = onScreen.size === 0;
            });
            blockers.forEach((el) => this.observer.observe(el));
        },
        destroy() {
            this.observer?.disconnect();
        },
        /**
         * A same-page link (#book, #reserve, #treatments) scrolls to the booking
         * natively: smooth, or instant under reduced motion (html's scroll-behavior).
         * Focus follows it there, so the next Tab starts in the booking, not at the top
         * of the page. The target is a container with tabindex="-1" (set here if the
         * markup didn't) and focus:outline-none; preventScroll leaves the scrolling to
         * the browser. Any other link (the site-wide bar's /rooms) is left alone.
         */
        follow(event) {
            const hash = event.currentTarget.hash;
            const target = hash && hash.length > 1 ? document.getElementById(decodeURIComponent(hash.slice(1))) : null;
            if (!target) return;
            if (!target.hasAttribute('tabindex')) target.setAttribute('tabindex', '-1');
            requestAnimationFrame(() => target.focus({ preventScroll: true }));
        },
    }));

    /**
     * Shared gallery lightbox. Callers render `{{ partial:lightbox }}` inside an
     * `x-data="lightbox([...])"` root and pass an array of `{ s: <full-size url>,
     * c: <caption> }`; clickable tiles in that root call `open(<full-size url>)`.
     * Duplicate sources are collapsed so the same photo never shows twice. The
     * partial is the modal dialog: its `x-trap.inert.noscroll="lb"` moves focus in
     * and hands it back to the opener when `lb` turns false, however it was set.
     * Captions arrive inside a JS string in an attribute, so callers escape them
     * with `| add_slashes | entities` (Statamic has no `addslashes` modifier).
     */
    Alpine.data('lightbox', (imgs = []) => ({
        lb: false,
        i: 0,
        imgs: imgs.filter((m, idx, arr) => arr.findIndex((x) => x.s === m.s) === idx),
        open(s) {
            this.i = Math.max(0, this.imgs.findIndex((m) => m.s === s));
            this.lb = true;
        },
        go(d) {
            this.i = (this.i + d + this.imgs.length) % this.imgs.length;
        },
    }));

    Alpine.data('listbox', listbox);

    /**
     * The home hero's Treatment tab: a date, then a treatment. The date is a Resrv
     * single-date search (view="availability-search-field", live off) inside
     * `x-ref="date"`; the treatment is the listbox above, whose `value` is the chosen
     * treatment's URL or '' for "Any treatment". `findTime()` opens that page, or
     * `anyUrl` (the spa's live listing), on the chosen day through Resrv's `?date=`
     * deep link, so the page opens on that day even if the session write that the
     * date pick started hasn't landed yet. With no date it asks for one the way the
     * search buttons do: the date field shows its prompt and opens the calendar.
     */
    Alpine.data('treatmentSearch', (placeholder = '', anyUrl = '/') => ({
        ...listbox(placeholder),
        findTime() {
            const root = this.$refs.date?.querySelector('[wire\\:id]');
            const search = root ? window.Livewire?.find(root.getAttribute('wire:id')) : null;
            const date = search?.data?.dates?.date_start;

            if (!date) {
                if (search) this.$dispatch('availability-dates-required', { id: search.$id });
                return;
            }

            const url = new URL(this.value || anyUrl, window.location.href);
            url.searchParams.set('date', String(date).slice(0, 10));
            window.location.assign(url.toString());
        },
    }));
});

// Re-scan reveals after Livewire SPA navigations.
document.addEventListener('livewire:navigated', () => initReveal());
