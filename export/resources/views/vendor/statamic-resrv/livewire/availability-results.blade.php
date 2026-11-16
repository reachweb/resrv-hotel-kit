@use(Carbon\Carbon)

{{-- Restyled to the Resrv Hotel design (rate rows live in
     components/availability-results-advanced). Markup/classes only — all
     wire: directives and the component structure are preserved.

     Kit hardening (keep through 3-way merges on upgrade):
     · The results sit in an inner wrapper that is aria-busy while a search loads; the
       one "Updating availability…" status lives outside it, since a screen reader may
       hold back live updates inside a busy region.
     · Errors are role="alert", so they are announced when a Livewire update adds them.
     · Messages use <p> (stock had bare dt/dd outside a dl). --}}
<div class="relative">
    <div wire:loading.delay.long.attr="aria-busy">
    @if ($rates == true && (! $data->rate || $data->rate === 'any'))
    <x-resrv::availability-results-advanced :$availability :entryRates="$this->entryRates" :dateStart="$data->dates['date_start'] ?? null" />
    @else
        @if (data_get($availability, 'message.status') === true)
            @if ($this->showOptions)
            <div class="flex flex-col gap-y-6 my-4">
                <livewire:options
                    :data="$this->data"
                    :filter="$this->showOptions"
                    :entryId="$this->entry->id"
                    :effectiveRateId="$this->effectiveRateId()"
                />
            </div>
            @endif
            @if ($this->showExtras)
            <div class="flex flex-col gap-y-6 my-4">
                <livewire:extras
                    :data="$this->data"
                    :filter="$this->showExtras"
                    :entryId="$this->entry->id"
                    :effectiveRateId="$this->effectiveRateId()"
                />
            </div>
            @endif
        <div class="divide-y divide-line">
            <div class="flex flex-col pb-5">
                <div class="font-medium mb-2">{{ trans('statamic-resrv::frontend.yourSearch') }}</div>
                <dl class="text-sm space-y-1">
                    @if ((int) data_get($availability, 'request.days', 0) > 1 || $this->entry->collection()->handle() === 'rooms')
                    <div class="flex flex-wrap gap-x-1.5">
                        <dt class="text-muted">{{ ucfirst(trans('statamic-resrv::frontend.from')) }}:</dt>
                        <dd class="font-medium">{{ Carbon::parse($data->dates['date_start'])->format('D d M Y') }}</dd>
                    </div>
                    <div class="flex flex-wrap gap-x-1.5">
                        <dt class="text-muted">{{ ucfirst(trans('statamic-resrv::frontend.to')) }}:</dt>
                        <dd class="font-medium">{{ Carbon::parse($data->dates['date_end'])->format('D d M Y') }}</dd>
                    </div>
                    <div class="flex flex-wrap gap-x-1.5">
                        <dt class="text-muted">{{ ucfirst(trans('statamic-resrv::frontend.duration')) }}:</dt>
                        <dd class="font-medium">{{ data_get($availability, 'request.days') }} {{ trans('statamic-resrv::frontend.days') }}</dd>
                    </div>
                    @else
                    {{-- Single-date products (spa, restaurant): one date, no checkout range --}}
                    <div class="flex flex-wrap gap-x-1.5">
                        <dt class="text-muted">{{ ucfirst(trans('statamic-resrv::frontend.date')) }}:</dt>
                        <dd class="font-medium">{{ Carbon::parse($data->dates['date_start'])->format('D d M Y') }}</dd>
                    </div>
                    @if ($data->quantity > 1)
                    <div class="flex flex-wrap gap-x-1.5">
                        <dt class="text-muted">{{ trans('statamic-resrv::frontend.quantityLabel') }}:</dt>
                        <dd class="font-medium">× {{ $data->quantity }}</dd>
                    </div>
                    @endif
                    @endif
                </dl>
            </div>
            <div class="flex flex-col py-5">
                @include('statamic-resrv::livewire.components.partials.availability-results-pricing')
            </div>
        </div>
        <div class="mt-5">
            <x-resrv::kit-cta full wire:click="checkout()">{{ trans('statamic-resrv::frontend.bookNow') }}</x-resrv::kit-cta>
        </div>
        @elseif (data_get($availability, 'message.status') === false)
        <div class="rounded-lg bg-sand/60 p-5 text-center">
            <p class="t-h3 mb-1">{{ trans('statamic-resrv::frontend.noAvailability') }}</p>
            <p class="text-sm text-muted">{{ trans('statamic-resrv::frontend.tryAdjustingYourSearch') }}</p>
        </div>
        @elseif (! $data->hasDates())
        <div class="rounded-lg bg-sand/60 p-5 text-center">
            <p class="text-sm text-muted">{{ trans('statamic-resrv::frontend.pleaseSelectDates') }}</p>
        </div>
        @endif
    @endif
    @if ($errors->has('availability') && $data->hasDates())
    <div role="alert" class="rounded-lg border border-error/30 bg-error/5 p-5 mt-4">
        <p class="font-medium text-error mb-1">{{ trans('statamic-resrv::frontend.searchError') }}</p>
        <p class="text-sm text-muted">{{ $errors->first('availability') }}</p>
    </div>
    @endif
    @if ($errors->has('cutoff') && $data->hasDates())
    <div role="alert" class="rounded-lg border border-error/30 bg-error/5 p-5 mt-4">
        <p class="font-medium text-error mb-1">{{ trans('statamic-resrv::frontend.youAreTooLate') }}</p>
        <p class="text-sm text-muted">{{ $errors->first('cutoff') }}</p>
    </div>
    @endif
    </div>
    <div role="status" class="sr-only"><span wire:loading.delay.long>Updating availability…</span></div>
    <div class="absolute inset-0 bg-white/60" wire:loading.delay.long aria-hidden="true">
        <span class="flex items-center justify-center w-full h-full">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="animate-spin size-5 text-terracotta-dark" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0 3.181 3.183a8.25 8.25 0 0 0 13.803-3.7M4.031 9.865a8.25 8.25 0 0 1 13.803-3.7l3.181 3.182m0-4.991v4.99" />
            </svg>
        </span>
    </div>
</div>
