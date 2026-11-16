{{--
    The kit's outcome header for the restyled Resrv views: a status icon in a tinted
    circle, the centred page heading and a lead line. The Blade twin of the header in
    checkout-complete.antlers.html (same sizes and tints), so a guest who pays by link
    lands on the same confirmation as one who books through checkout. Not an upstream
    component: it renders as <x-resrv::kit-outcome>, like kit-cta.

    Props:
      tone    — 'success' (tick) · 'pending' (clock) · 'warning' (alert) · 'neutral' (muted alert)
      heading — the page's h1
    Slot: the lead text, plus anything that belongs under it (e.g. the business contact).
--}}
@props(['tone' => 'neutral', 'heading'])
<div {{ $attributes->class(['text-center mb-10']) }}>
    <div @class([
        'size-20 rounded-full grid place-items-center mx-auto mb-6',
        'bg-success/10 text-success' => $tone === 'success',
        'bg-warning/10 text-warning' => in_array($tone, ['pending', 'warning'], true),
        'bg-sand text-muted' => ! in_array($tone, ['success', 'pending', 'warning'], true),
    ])>
        @if ($tone === 'success')
        <svg class="size-9" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M20 6 9 17l-5-5"/></svg>
        @elseif ($tone === 'pending')
        <svg class="size-9" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/></svg>
        @else
        <svg class="size-9" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="12" r="9"/><path d="M12 7.5v5M12 16.5h.01"/></svg>
        @endif
    </div>
    <h1 class="t-h1 mb-3">{{ $heading }}</h1>
    <div class="text-lg leading-relaxed text-balance text-muted max-w-[52ch] mx-auto" role="status">{{ $slot }}</div>
</div>
