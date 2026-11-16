@props(['step', 'enableExtrasStep'])

{{-- Restyled to the design's progress row (checkout.html): numbered circles —
     done = success + check, current = teal, upcoming = sand — joined by hairlines.
     The goToStep() targets and the enableExtrasStep / zero-payment logic are stock;
     the steps are listed from one array so each <li> is written once. A step you can
     go back to is a real <button> carrying the stock wire:click (upstream put it on the
     <li>, which the keyboard can't reach). Inactive steps use full-strength Muted (no
     opacity), the current one is marked with aria-current, labels stay readable to
     screen readers on phones, and a done step says "completed". --}}
@php
    $hasPayment = ! $this->reservation->payment->isZero();
    $steps = collect([
        $enableExtrasStep ? ['step' => 1, 'label' => trans('statamic-resrv::frontend.extrasAndOptions'), 'target' => $step !== 1 ? 'goToStep(1)' : null] : null,
        ['step' => 2, 'label' => trans('statamic-resrv::frontend.customerInfo'), 'target' => $step > 2 ? 'goToStep(2)' : null],
        $hasPayment ? ['step' => 3, 'label' => trans('statamic-resrv::frontend.payment'), 'target' => null] : null,
    ])->filter()->values();
@endphp
<ol class="flex items-center gap-3 sm:gap-6 max-w-160">
    @foreach ($steps as $item)
    @php($isDone = $step > $item['step'])
    @php($isCurrent = $step === $item['step'])
    <li @class(['flex items-center gap-2.5', 'flex-1' => ! $loop->last]) @if ($isCurrent) aria-current="step" @endif>
        @if ($item['target'])
        <button type="button" wire:click="{{ $item['target'] }}" class="group flex items-center gap-2.5 shrink-0 rounded-md cursor-pointer">
        @else
        <span class="flex items-center gap-2.5 shrink-0">
        @endif
            <span aria-hidden="true" @class([
                'size-8 rounded-full grid place-items-center text-sm font-semibold shrink-0',
                'bg-success text-white' => $isDone,
                'bg-teal text-white' => $isCurrent,
                'bg-sand text-muted' => ! $isDone && ! $isCurrent,
            ])>
                @if ($isDone)
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" class="size-4"><path d="M20 6 9 17l-5-5"/></svg>
                @else
                    {{ $loop->iteration }}
                @endif
            </span>
            <span @class([
                'text-sm sr-only sm:not-sr-only',
                'font-semibold text-ink' => $isCurrent,
                'font-medium text-muted' => ! $isCurrent,
                'group-hover:text-ink transition-colors' => $item['target'],
            ])>
                {{ $item['label'] }}@if ($isDone)<span class="sr-only"> ({{ __('completed') }})</span>@endif
            </span>
        @if ($item['target'])
        </button>
        @else
        </span>
        @endif
        @unless ($loop->last)
        <span class="flex-1 h-px bg-line hidden sm:block" aria-hidden="true"></span>
        @endunless
    </li>
    @endforeach
</ol>
