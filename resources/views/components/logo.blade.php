<a href="{{ route('homepage') }}" class="absolute top-0 left-1/2 -translate-x-1/2 w-max p-1">
    {{-- Full logo: shown at the top of the page, hangs below the navbar --}}
    <img
        src="{{ asset('logos/faux-vero-logo.svg') }}"
        alt=""
        x-show="!scrolled"
        x-transition.opacity.duration.200ms
        class="absolute top-1 left-1/2 -translate-x-1/2 max-w-none size-40 md:size-50"
    />

    {{-- Horizontal logo: shown once the user scrolls, fits the navbar --}}
    <img
        src="{{ asset('logos/faux-vero-logo-horizontal.svg') }}"
        alt=""
        x-show="scrolled"
        x-cloak
        x-transition.opacity.duration.200ms
        class="h-8 max-w-none"
    />

    <span class="sr-only">Faux Vero</span>
</a>
