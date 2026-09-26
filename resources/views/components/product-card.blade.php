<article class="flex flex-col items-stretch pb-6">
    <a href="/products/{{ $slug }}" class="aspect-square bg-gray-200 flex items-center justify-center">
        @if ($image)
        <img src="{{ $image }}" alt="{{ $title }}" class="object-cover w-full h-full ">
    @endif

    </a>
    <header class="flex flex-row items-start justify-between gap-2">
        <h3 class="text-lg font-semibold">
            <a href="/products/{{ $slug }}">
            {{ $title }}
            </a>
        </h3>
        <p class="text-lg font-semibold">{{ $price }} €</p>
    </header>
            <p class="">{{ $type }} €</p>

</article>