@php($srcset = $asset->srcset())
<img
    src="{{ $asset->url('web') }}"
    @if ($srcset) srcset="{{ $srcset }}" sizes="{{ $sizes }}" @endif
    alt="{{ $alt ?? $asset->alt }}"
    loading="{{ $loading }}"
    decoding="async"
    {{ $attributes }}
>
