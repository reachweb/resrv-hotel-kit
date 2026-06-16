/**
 * Resrv Hotel — front-end progressive enhancement.
 *
 * Small, one-off stateful UI (mobile sheet, accordion) stays inline in the
 * templates — the Alpine that Livewire 4 bundles. Centralised here: the
 * reveal-on-scroll observer, and the `lightbox` Alpine component shared by the
 * room gallery and the page-builder gallery grid (registered on `alpine:init`,
 * which is why site.js loads in <head>, before {{ livewire:scripts }}).
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
// Re-scan after Livewire SPA navigations and component updates.
document.addEventListener('livewire:navigated', () => initReveal());

/**
 * Shared gallery lightbox. Callers render `{{ partial:lightbox }}` inside an
 * `x-data="lightbox([...])"` root and pass an array of `{ s: <full-size url>,
 * c: <caption> }`; clickable tiles in that root call `open(<full-size url>)`.
 * Duplicate sources (e.g. a hero that also appears in the gallery) are collapsed
 * so the same photo never shows twice.
 */
document.addEventListener('alpine:init', () => {
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
});
