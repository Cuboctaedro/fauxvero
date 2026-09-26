<article class="flex flex-col items-stretch pb-6">
    <a href="/products/{{ $slug }}" class="aspect-square bg-gray-200 flex items-center justify-center">
        <x-asset-image
            :asset="$asset"
            :alt="$title"
            sizes="(min-width: 1280px) 300px, (min-width: 1024px) 25vw, (min-width: 768px) 33vw, (min-width: 640px) 50vw, 100vw"
            class="object-cover w-full h-full"
        />

    </a>
    <header >
        <h3 class="text-lg font-semibold">
            <a href="/products/{{ $slug }}">
            {{ $title }}
            </a>
        </h3>
        
    </header>
    <div class="flex flex-row items-start justify-between gap-2">
        <p class="">{{ $type }}</p>
        @if ($price !== null)
            <p class=" font-semibold">{{ $price }} €</p>
        @endif
    </div>
    @unless ($inStock)
        <x-out-of-stock class="self-start mt-1" />
    @endunless

</article>