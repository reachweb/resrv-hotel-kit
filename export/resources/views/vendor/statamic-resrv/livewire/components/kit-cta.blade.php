{{--
    The kit's call to action for the restyled Resrv views: the Blade twin of
    partials/button (primary Terracotta Dark with white text, or the teal outline
    secondary). Not an upstream component: Resrv registers this folder as the
    `x-resrv::` anonymous-component path, so it renders as <x-resrv::kit-cta>.
    Every attribute passes through to the <button>, so wire:, x-/@ directives and
    `disabled` work as written. Inside a component tag, write Alpine's `:attr`
    shorthand as `x-bind:attr` (or `::attr`); a single colon makes Blade evaluate
    it as PHP.

    Props:
      variant — 'primary' (default) · 'secondary' (teal outline)
      size    — 'md' (48px, default) · 'sm' (40px)
      full    — true for a full-width button
      href    — renders a link instead (a way forward that isn't an action)
--}}
@props(['variant' => 'primary', 'size' => 'md', 'full' => false, 'href' => null])
@php($tag = $href ? 'a' : 'button')
<{{ $tag }} {{ ($href ? $attributes->merge(['href' => $href]) : $attributes->merge(['type' => 'button']))->class([
    'relative inline-flex items-center justify-center gap-2 rounded-lg border-[1.5px] font-semibold leading-none whitespace-nowrap transition cursor-pointer disabled:cursor-not-allowed disabled:opacity-70',
    'border-teal bg-transparent text-teal hover:bg-teal hover:text-white' => $variant === 'secondary',
    'border-transparent bg-terracotta-dark text-white hover:bg-terracotta-deep' => $variant !== 'secondary',
    'h-10 px-4.5 text-sm' => $size === 'sm',
    'h-12 px-6 text-ui' => $size !== 'sm',
    'w-full' => $full,
]) }}>{{ $slot }}</{{ $tag }}>
