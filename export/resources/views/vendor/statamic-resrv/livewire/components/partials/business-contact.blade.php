{{-- Restyled to the kit's muted text + teal links. Values and guards are stock. --}}
@php($contactMail = config('resrv-config.mail'))
@php($contactPhone = config('resrv-config.phone'))
@if (is_string($contactMail) && filled($contactMail))
<p class="text-sm text-muted break-words">{{ trans('statamic-resrv::frontend.emailAddress') }}: <a href="mailto:{{ $contactMail }}" class="text-teal underline underline-offset-2">{{ $contactMail }}</a></p>
@endif
@if (is_scalar($contactPhone) && filled($contactPhone))
<p class="text-sm text-muted break-words">{{ trans('statamic-resrv::frontend.contactPhone') }}: <a href="tel:{{ preg_replace('/[^\d+]/', '', (string) $contactPhone) }}" class="text-teal underline underline-offset-2">{{ $contactPhone }}</a></p>
@endif
