{{-- Restyled to the brand error card. Structure stock.
     Kit addition: a way forward. Opening /checkout with no booking in progress (a new
     tab, an expired session) has no useful "previous page", often only this one, so
     the primary action starts a new search from the home page and the previous-page
     link shows only when it leads somewhere else (with no referrer Laravel falls back
     to the home page, which the primary action already covers). --}}
@php($previousUrl = rtrim(url()->previous(), '/'))
@php($showPrevious = ! in_array($previousUrl, [rtrim(url()->current(), '/'), rtrim(url('/'), '/')], true))
<div>
    <div class="rounded-lg border border-error/30 bg-error/5 p-5 my-4 lg:my-6" role="alert">
        <p class="text-lg font-medium text-error">{{ trans('statamic-resrv::frontend.somethingWentWrong') }}</p>
        <p class="text-sm text-muted mt-1">{{ $message }}</p>
    </div>
    <div class="flex flex-wrap items-center gap-x-6 gap-y-4">
        <x-resrv::kit-cta :href="url('/')">{{ trans('statamic-resrv::frontend.startNewSearch') }}</x-resrv::kit-cta>
        @if ($showPrevious)
        <a class="inline-flex items-center gap-2 text-ui font-medium text-ink underline-offset-4 hover:text-terracotta-dark hover:underline transition-colors" href="{{ $previousUrl }}">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-5" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 15 3 9m0 0 6-6M3 9h12a6 6 0 0 1 0 12h-3" />
            </svg>
            {{ trans('statamic-resrv::frontend.returnToThePreviousPage') }}
        </a>
        @endif
    </div>
</div>
