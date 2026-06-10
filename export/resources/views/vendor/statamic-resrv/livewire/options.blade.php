{{-- Restyled to the design's "Your room options" card (checkout.html step 1) —
     options render as segmented pill controls. Livewire structure stock. --}}
<div>
    @if ($this->options->count() > 0)
        <div class="bg-white rounded-lg shadow-card p-5 lg:p-6 mb-8">
            <h3 class="text-xl font-semibold mb-1">{{ trans('statamic-resrv::frontend.options') }}</h3>
            <p class="text-sm text-muted mb-5">{{ trans('statamic-resrv::frontend.optionsDescription') }}</p>
            <div class="space-y-6">
                @foreach ($this->options as $id => $option)
                @include('statamic-resrv::livewire.components.partials.option')
                @endforeach
            </div>
            @if ($errors)
            <div class="rounded-lg border border-error/30 bg-error/5 p-4 mt-5">
                <div class="text-sm text-error">
                    @foreach ($errors as $index => $error)
                        <div wire:key="{{ $index }}">{{ $error }}</div>
                    @endforeach
                </div>
            </div>
            @endif
        </div>
    @endif
</div>
