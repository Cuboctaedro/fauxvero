<x-layout>
    <x-product-schema
        :title="$product->name"
        :description="$product->description"
        :image="$product->featuredImage"
        :price="$product->price"
        :slug="$product->slug"
    />

    <main>
        Product content goes here.
    </main>

</x-layout>
