<x-layout :meta-title="__('Cart').' | Fauxvero'" :meta-robots="false">
    <section x-data="cartLines(@js(route('cart.lines')))" class="max-w-4xl">
        <h1 class="text-3xl font-bold mb-6">{{ __('Cart') }}</h1>

        <p x-show="loading" class="text-gray-600">{{ __('Loading…') }}</p>

        <p x-cloak x-show="failed" class="text-red-700" role="alert">
            {{ __('The cart could not be loaded. Please try again.') }}
        </p>

        <div x-cloak x-show="!loading && !failed && lines.length === 0">
            <p class="mb-4">{{ __('Your cart is empty.') }}</p>
            <a href="{{ route('products.index') }}" class="underline">{{ __('Browse products') }}</a>
        </div>

        <div x-cloak x-show="!loading && !failed && lines.length > 0">
            <ul class="divide-y divide-gray-400 border-y border-gray-400">
                <template x-for="line in lines" :key="line.id">
                    <li class="py-4 grid grid-cols-[5rem_1fr] sm:grid-cols-[6rem_1fr_auto] gap-4 items-start">
                        <a :href="line.url" class="aspect-square bg-gray-300 block">
                            <template x-if="line.image">
                                <img :src="line.image" :alt="line.name" class="object-cover w-full h-full">
                            </template>
                        </a>

                        <div>
                            <h2 class="font-semibold">
                                <a :href="line.url" x-text="line.name"></a>
                            </h2>
                            <p class="text-gray-600" x-text="line.type"></p>
                            <p x-text="line.unit_price + ' €'"></p>
                            <p x-show="!line.available" class="text-red-700 font-semibold">{{ __('Unavailable') }}</p>
                        </div>

                        <div class="col-span-2 sm:col-span-1 flex sm:flex-col items-center sm:items-end justify-between gap-2">
                            <div class="flex items-center border border-gray-500">
                                <button
                                    type="button"
                                    class="w-9 h-9 disabled:opacity-40"
                                    :disabled="line.qty <= 1"
                                    @click="$store.cart.setQty(line.id, line.qty - 1)"
                                    :aria-label="@js(__('Decrease quantity for')) + ' ' + line.name"
                                >−</button>
                                <input
                                    type="number"
                                    min="1"
                                    max="{{ \App\Support\Cart::MAX_QUANTITY }}"
                                    class="w-12 h-9 text-center bg-transparent [appearance:textfield]"
                                    :value="line.qty"
                                    @change="$store.cart.setQty(line.id, Math.min($event.target.valueAsNumber, {{ \App\Support\Cart::MAX_QUANTITY }}))"
                                    :aria-label="@js(__('Quantity for')) + ' ' + line.name"
                                >
                                <button
                                    type="button"
                                    class="w-9 h-9 disabled:opacity-40"
                                    :disabled="line.qty >= {{ \App\Support\Cart::MAX_QUANTITY }}"
                                    @click="$store.cart.setQty(line.id, line.qty + 1)"
                                    :aria-label="@js(__('Increase quantity for')) + ' ' + line.name"
                                >+</button>
                            </div>
                            <p class="font-semibold" x-text="line.line_total + ' €'"></p>
                            <button type="button" class="text-sm underline" @click="$store.cart.remove(line.id)">
                                {{ __('Remove') }}
                            </button>
                        </div>
                    </li>
                </template>
            </ul>

            <div class="mt-6 flex flex-col items-end gap-4">
                <p class="text-xl">
                    {{ __('Total') }}: <span class="font-bold" x-text="subtotal + ' €'"></span>
                </p>

                <p x-show="!purchasable" class="text-red-700">
                    {{ __('Remove the unavailable products to continue.') }}
                </p>

                <a
                    href="{{ route('checkout.index') }}"
                    class="py-3 px-6 bg-black text-white dark:bg-gray-100 dark:text-black uppercase tracking-wider font-bold hover:opacity-80 transition-opacity"
                    :class="{ 'pointer-events-none opacity-40': !purchasable }"
                    :aria-disabled="!purchasable"
                >
                    {{ __('Proceed to checkout') }}
                </a>
            </div>
        </div>
    </section>
</x-layout>
