/**
 * Resrv Hotel — front-end progressive enhancement.
 *
 * Small, one-off stateful UI (mobile sheet, accordion) stays inline in the
 * templates — the Alpine that Livewire 4 bundles. Centralised here: the
 * reveal-on-scroll observer, the dismissable announcement-bar store, and the
 * `lightbox` Alpine component shared by the room gallery and the page-builder
 * gallery grid (registered on `alpine:init`, which is why site.js loads in
 * <head>, before {{ livewire:scripts }}).
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

// Announcement-bar dismissal is remembered in localStorage for 24h.
const ANNOUNCEMENT_TTL = 24 * 60 * 60 * 1000; // 24 hours

document.addEventListener('alpine:init', () => {
    /**
     * Announcement bar visibility — shared by the bar partial (x-show), the fixed
     * header offset, and the body padding. `sync()` toggles `.has-promo` on <body>
     * (which site.css reads via --promo-h) and no-ops when no bar is rendered, so
     * disabled pages are untouched.
     */
    Alpine.store('announcement', {
        open: true,
        init() {
            let dismissedAt = 0;
            try {
                dismissedAt = Number(localStorage.getItem('announcementDismissedAt')) || 0;
            } catch (e) {
                // localStorage unavailable (private mode) — keep the bar visible.
            }
            this.open = (Date.now() - dismissedAt) > ANNOUNCEMENT_TTL;
            this.sync();
        },
        dismiss() {
            this.open = false;
            try {
                localStorage.setItem('announcementDismissedAt', String(Date.now()));
            } catch (e) {
                // Ignore — dismissal just won't persist across reloads.
            }
            this.sync();
        },
        sync() {
            if (!document.getElementById('announcementBar')) return;
            document.body.classList.toggle('has-promo', this.open);
        },
    });

    /**
     * Shared gallery lightbox. Callers render `{{ partial:lightbox }}` inside an
     * `x-data="lightbox([...])"` root and pass an array of `{ s: <full-size url>,
     * c: <caption> }`; clickable tiles in that root call `open(<full-size url>)`.
     * Duplicate sources are collapsed so the same photo never shows twice.
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
});

// Re-scan reveals + re-apply the body offset after Livewire SPA navigations.
document.addEventListener('livewire:navigated', () => {
    initReveal();
    window.Alpine?.store('announcement')?.sync();
});
