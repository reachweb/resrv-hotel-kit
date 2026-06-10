{{-- Restyled to the design's category-grouped extras (checkout.html step 1):
     category heading + 2-col grid of extra cards. Livewire structure stock. --}}
<div>
    @if ($this->frontendExtras->count() > 0)
        <div class="space-y-8">
            @foreach ($this->frontendExtras as $category)
            <div>
                <div class="mb-4">
                    <h3 class="text-xl font-semibold">{{ $category->id ? $category->name : trans('statamic-resrv::frontend.extras') }}</h3>
                    @if ($category->id && $category->description)
                    <p class="text-sm text-muted mt-0.5">{{ $category->description }}</p>
                    @elseif (! $category->id)
                    <p class="text-sm text-muted mt-0.5">{{ trans('statamic-resrv::frontend.extrasDescription') }}</p>
                    @endif
                </div>
                <div class="grid sm:grid-cols-2 gap-4">
                    @foreach ($category->extras as $extra)
                    @include('statamic-resrv::livewire.components.partials.extra')
                    @endforeach
                </div>
            </div>
            @endforeach
        </div>
        @if ($errors)
        <div class="rounded-lg border border-error/30 bg-error/5 p-4 mt-6">
            <div class="text-sm text-error">
                @foreach ($errors as $index => $error)
                    <div wire:key="{{ $index }}">{{ $error }}</div>
                @endforeach
            </div>
        </div>
        @endif
    @endif
</div>
