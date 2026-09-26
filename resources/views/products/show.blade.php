<x-layout>
    <x-product-schema
        :title="$product->name"
        :description="$product->description"
        :image="$product->featuredImage"
        :price="$product->price"
        :slug="$product->slug"
    />

    <section>
        <header class="mb-4 grid grid-cols-1 sm:grid-cols-4 md:grid-cols-8 lg:grid-cols-12 gap-4 items-start">
            <div class="col-span-1 sm:col-span-3 md:col-span-6 lg:col-span-10">
                <h1 class="text-3xl font-bold">{{ $product->name }}</h1>
                <p class="text-lg text-gray-600">{{ $product->type }}</p>
            </div>
            <div class="col-span-1 sm:col-span-1 md:col-span-2">
                <p class="text-lg font-semibold text-right">{{ $product->price }} €</p>
            </div>
            
        </header>

        <div class="grid grid-cols-1 sm:grid-cols-4 md:grid-cols-8 lg:grid-cols-12 gap-4 items-start">
 
            <div class="col-span-1 sm:col-span-4 md:col-span-8">
                @foreach($product->galleryAssets as $asset)
                    <div class=" bg-gray-200 flex items-center justify-center mb-4">
                        <img src="{{ $asset->url('web') }}" alt="{{ $asset->alt ?: $product->name }}" class="object-cover w-full h-full ">
                    </div>
                @endforeach

            </div>

            <div class="col-span-1 sm:col-span-4 md:col-span-8 lg:col-span-4">
                @if (! $product->in_stock)
                    <x-out-of-stock class="mb-8" />
                @elseif ($product->price !== null)
                    <x-add-to-cart :product-id="$product->id" class="mb-8" />
                @endif
                <section class="content text-lg mb-12">
                    <h2 class="sr-only">Περιγραφή</h2>
                    {{ \Filament\Forms\Components\RichEditor\RichContentRenderer::make($product->description) }}
                </section>
                <section>
                    <h2 class="sr-only">Χαρακτηριστικά</h2>
                    @if($product->dimensions)
                        <h3 class="font-bold my-2">Διαστάσεις</h3>
                        <p>{{ $product->dimensions }}</p>
                    @endif
                    @if($product->weight)
                        <h3 class="font-bold my-2">Βάρος</h3>
                        <p>{{ $product->weight }}</p>
                    @endif
                    @if($product->material)
                        <h3 class="font-bold my-2">Υλικό</h3>
                        <p>{{ $product->material }}</p>
                    @endif
                </section>
            </div>
        </div>
    </section>

</x-layout>
