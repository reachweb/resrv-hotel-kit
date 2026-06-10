{{-- Restyled to the brand error card. Structure stock. --}}
<div>
    <div class="rounded-lg border border-error/30 bg-error/5 p-5 my-4 lg:my-6">
        <p class="text-lg font-medium text-error">{{ trans('statamic-resrv::frontend.somethingWentWrong') }}</p>
        <p class="text-sm text-muted mt-1">{{ $message }}</p>
    </div>
    <a class="inline-flex items-center gap-3 font-medium bg-white rounded-lg shadow-card px-5 py-4 hover:text-terracotta-dark transition-colors" href="{{ url()->previous() }}">
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5">
            <path stroke-linecap="round" stroke-linejoin="round" d="M9 15 3 9m0 0 6-6M3 9h12a6 6 0 0 1 0 12h-3" />
        </svg>
        {{ trans('statamic-resrv::frontend.returnToThePreviousPage') }}
    </a>
</div>
