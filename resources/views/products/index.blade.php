<x-layout>

    <main>

        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4 items-start">

            @foreach ($products as $product)
                <x-product-card
                    :title="$product->name"
                    :type="$product->type"
                    :asset="$product->featuredAsset"
                    :price="$product->price"
                    :slug="$product->slug"
                    :in-stock="$product->in_stock"
                />
            @endforeach

        </div>
    </main>

</x-layout>
