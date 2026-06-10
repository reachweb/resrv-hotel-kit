@props(['step', 'enableExtrasStep'])

{{-- Restyled to the design's progress row (checkout.html): numbered circles —
     done = success + check, current = teal, upcoming = sand — joined by hairlines.
     The wire:click step-navigation and the enableExtrasStep / zero-payment logic
     are stock — markup/classes only. --}}
<ol class="flex items-center gap-3 sm:gap-6 max-w-[640px]">
    @if ($enableExtrasStep)
    <li
        wire:click="{{ $step !== 1 ? 'goToStep(1)' : '' }}"
        @class([
            'flex items-center gap-2.5 flex-1 transition-opacity duration-300',
            'opacity-60' => $step !== 1,
            'cursor-pointer' => $step !== 1,
        ])
    >
        <span @class([
            'w-8 h-8 rounded-full grid place-items-center text-[13px] font-semibold shrink-0',
            'bg-success text-white' => $step > 1,
            'bg-teal text-white' => $step === 1,
        ])>
            @if ($step > 1)
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" class="w-4 h-4"><path d="M20 6 9 17l-5-5"/></svg>
            @else
                1
            @endif
        </span>
        <span @class(['text-sm font-medium hidden sm:block', 'text-ink' => $step === 1, 'text-muted' => $step !== 1])>
            {{ trans('statamic-resrv::frontend.extrasAndOptions') }}
        </span>
        <span class="flex-1 h-px bg-line hidden sm:block"></span>
    </li>
    @endif
    <li
        wire:click="{{ $step > 2 ? 'goToStep(2)' : '' }}"
        @class([
            'flex items-center gap-2.5 transition-opacity duration-300',
            'flex-1' => ! $this->reservation->payment->isZero(),
            'opacity-60' => $step !== 2,
            'cursor-pointer' => $step > 2,
        ])
    >
        <span @class([
            'w-8 h-8 rounded-full grid place-items-center text-[13px] font-semibold shrink-0',
            'bg-success text-white' => $step > 2,
            'bg-teal text-white' => $step === 2,
            'bg-sand text-muted' => $step < 2,
        ])>
            @if ($step > 2)
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" class="w-4 h-4"><path d="M20 6 9 17l-5-5"/></svg>
            @else
                {{ $enableExtrasStep === false ? '1' : '2' }}
            @endif
        </span>
        <span @class(['text-sm font-medium hidden sm:block', 'text-ink' => $step === 2, 'text-muted' => $step !== 2])>
            {{ trans('statamic-resrv::frontend.customerInfo') }}
        </span>
        @if (! $this->reservation->payment->isZero())
        <span class="flex-1 h-px bg-line hidden sm:block"></span>
        @endif
    </li>
    @if (! $this->reservation->payment->isZero())
    <li @class(['flex items-center gap-2.5 transition-opacity duration-300', 'opacity-60' => $step !== 3])>
        <span @class([
            'w-8 h-8 rounded-full grid place-items-center text-[13px] font-semibold shrink-0',
            'bg-teal text-white' => $step === 3,
            'bg-sand text-muted' => $step < 3,
        ])>
            {{ $enableExtrasStep === false ? '2' : '3' }}
        </span>
        <span @class(['text-sm font-medium hidden sm:block', 'text-ink' => $step === 3, 'text-muted' => $step !== 3])>
            {{ trans('statamic-resrv::frontend.payment') }}
        </span>
    </li>
    @endif
</ol>
