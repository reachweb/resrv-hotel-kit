/**
 * Resrv Hotel — front-end progressive enhancement.
 *
 * Stateful UI (mobile sheet, accordion, gallery filter/lightbox) is built with
 * Alpine inline in the templates — the Alpine that Livewire 4 bundles. The only
 * thing centralised here is reveal-on-scroll, which is a global, stateless
 * enhancement and not worth an Alpine plugin (intersect isn't bundled).
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
