{{-- Restyled to the design's category-grouped extras (checkout.html step 1):
     category heading + 2-col grid of extra cards. Livewire structure stock.
     A group whose extras are all hidden by extra conditions (e.g. a conditional
     extra that needs a longer stay) is hidden with them, heading included — kept
     in the DOM like the hidden cards so Livewire morphs it back in when it applies.
     Category headings only show when more than one group is visible: categories
     are global in Resrv, so a single-group product (e.g. a table booking with one
     cross-product extra) would otherwise inherit a heading written for another. --}}
<div>
    @if ($this->frontendExtras->count() > 0)
        @php($visibleGroups = $this->frontendExtras->reject(
            fn ($category) => $category->extras->every(fn ($extra) => $this->hiddenExtras->contains($extra->id))
        )->count())
        <div class="flex flex-col gap-8">
            @foreach ($this->frontendExtras as $category)
            <div @class(['hidden' => $category->extras->every(fn ($extra) => $this->hiddenExtras->contains($extra->id))])>
                @if ($visibleGroups > 1)
                <div class="mb-4">
                    <h3 class="text-xl font-semibold">{{ $category->id ? $category->name : trans('statamic-resrv::frontend.extras') }}</h3>
                    @if ($category->id && $category->description)
                    <p class="text-sm text-muted mt-0.5">{{ $category->description }}</p>
                    @elseif (! $category->id)
                    <p class="text-sm text-muted mt-0.5">{{ trans('statamic-resrv::frontend.extrasDescription') }}</p>
                    @endif
                </div>
                @endif
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
