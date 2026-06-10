@use(Carbon\Carbon)

{{-- Restyled to the Resrv Hotel design (rate rows live in
     components/availability-results-advanced). Markup/classes only — all
     wire: directives and the component structure are preserved. --}}
<div class="relative">
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
                />
            </div>
            @endif
            @if ($this->showExtras)
            <div class="flex flex-col gap-y-6 my-4">
                <livewire:extras
                    :data="$this->data"
                    :filter="$this->showExtras"
                    :entryId="$this->entry->id"
                />
            </div>
            @endif
        <div class="divide-y divide-line">
            <div class="flex flex-col pb-5">
                <div class="font-medium mb-2">{{ trans('statamic-resrv::frontend.yourSearch') }}</div>
                <div class="text-sm space-y-1">
                    @if ((int) data_get($availability, 'request.days', 0) > 1 || $this->entry->collection()->handle() === 'rooms')
                    <div>
                        <span class="text-muted">{{ ucfirst(trans('statamic-resrv::frontend.from')) }}:</span>
                        <span class="font-medium">{{ Carbon::parse($data->dates['date_start'])->format('D d M Y') }}</span>
                    </div>
                    <div>
                        <span class="text-muted">{{ ucfirst(trans('statamic-resrv::frontend.to')) }}:</span>
                        <span class="font-medium">{{ Carbon::parse($data->dates['date_end'])->format('D d M Y') }}</span>
                    </div>
                    <div>
                        <span class="text-muted">{{ ucfirst(trans('statamic-resrv::frontend.duration')) }}:</span>
                        <span class="font-medium">{{ data_get($availability, 'request.days') }} {{ trans('statamic-resrv::frontend.days') }}</span>
                    </div>
                    @else
                    {{-- Single-date products (spa, restaurant): one date, no checkout range --}}
                    <div>
                        <span class="text-muted">{{ ucfirst(trans('statamic-resrv::frontend.date')) }}:</span>
                        <span class="font-medium">{{ Carbon::parse($data->dates['date_start'])->format('D d M Y') }}</span>
                    </div>
                    @if ($data->quantity > 1)
                    <div>
                        <span class="text-muted">{{ trans('statamic-resrv::frontend.quantityLabel') }}:</span>
                        <span class="font-medium">× {{ $data->quantity }}</span>
                    </div>
                    @endif
                    @endif
                </div>
            </div>
            <div class="flex flex-col py-5">
                @include('statamic-resrv::livewire.components.partials.availability-results-pricing')
            </div>
        </div>
        <div class="mt-5">
            <button
                type="button"
                class="inline-flex items-center justify-center gap-2 w-full h-12 px-6 rounded-lg border-[1.5px] border-transparent text-[15px] font-semibold leading-none whitespace-nowrap transition bg-terracotta text-white hover:bg-terracotta-dark focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-terracotta"
                wire:click="checkout()"
            >
                {{ trans('statamic-resrv::frontend.bookNow') }}
            </button>
        </div>
        @elseif (data_get($availability, 'message.status') === false)
        <div class="rounded-lg border border-line bg-sand/60 p-5 text-center">
            <dt class="t-h3 mb-1">{{ trans('statamic-resrv::frontend.noAvailability') }}</dt>
            <dd class="text-sm text-muted">{{ trans('statamic-resrv::frontend.tryAdjustingYourSearch') }}</dd>
        </div>
        @elseif (! $data->hasDates())
        <div class="rounded-lg border border-line bg-sand/60 p-5 text-center">
            <dt class="text-sm text-muted">{{ trans('statamic-resrv::frontend.pleaseSelectDates') }}</dt>
        </div>
        @endif
    @endif
    @if ($errors->has('availability') && $data->hasDates())
    <div class="rounded-lg border border-error/30 bg-error/5 p-5 mt-4">
        <dt class="font-medium text-error mb-1">{{ trans('statamic-resrv::frontend.searchError') }}</dt>
        <dd class="text-sm text-muted">{{ $errors->first('availability') }}</dd>
    </div>
    @endif
    @if ($errors->has('cutoff') && $data->hasDates())
    <div class="rounded-lg border border-error/30 bg-error/5 p-5 mt-4">
        <dt class="font-medium text-error mb-1">{{ trans('statamic-resrv::frontend.youAreTooLate') }}</dt>
        <dd class="text-sm text-muted">{{ $errors->first('cutoff') }}</dd>
    </div>
    @endif
    <div class="absolute left-0 right-0 top-0 w-full h-full bg-white/60" wire:loading.delay.long>
        <span class="flex items-center justify-center w-full h-full">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="animate-spin w-5 h-5 text-terracotta">
                <path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0 3.181 3.183a8.25 8.25 0 0 0 13.803-3.7M4.031 9.865a8.25 8.25 0 0 1 13.803-3.7l3.181 3.182m0-4.991v4.99" />
            </svg>
        </span>
    </div>
</div>
