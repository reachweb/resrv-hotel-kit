{{--
    The kit's primary call to action for the restyled Resrv views (Terracotta Dark,
    white text, 2px focus ring). Not an upstream component: Resrv registers this
    folder as the `x-resrv::` anonymous-component path, so it renders as
    <x-resrv::kit-cta>. Every attribute passes through to the <button>, so wire:,
    x-/@ directives and `disabled` work as written. Inside a component tag, write
    Alpine's `:attr` shorthand as `x-bind:attr` (or `::attr`); a single colon makes
    Blade evaluate it as PHP.

    Props:
      size — 'md' (48px, default) · 'sm' (40px)
      full — true for a full-width button
--}}
@props(['size' => 'md', 'full' => false])
<button {{ $attributes->merge(['type' => 'button'])->class([
    'relative inline-flex items-center justify-center gap-2 rounded-lg border-[1.5px] border-transparent bg-terracotta-dark font-semibold leading-none whitespace-nowrap text-white transition cursor-pointer hover:bg-terracotta-deep disabled:cursor-not-allowed disabled:opacity-70',
    'h-10 px-4.5 text-sm' => $size === 'sm',
    'h-12 px-6 text-ui' => $size !== 'sm',
    'w-full' => $full,
]) }}>{{ $slot }}</button>
