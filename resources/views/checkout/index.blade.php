<x-layout meta-title="Ταμείο | Fauxvero" :meta-robots="false" page-path="/checkout">
    <section x-data="cartLines">
        <h1 class="text-3xl font-bold mb-6">Ταμείο</h1>

        @error('items')
            <p class="mb-6 p-4 border border-red-700 text-red-700" role="alert">
                {{ $message }} <a href="{{ route('cart.index') }}" class="underline">Μετάβαση στο καλάθι</a>
            </p>
        @enderror

        <p x-show="loading" class="text-gray-600">Φόρτωση…</p>

        <p x-cloak x-show="failed" class="text-red-700" role="alert">
            Δεν ήταν δυνατή η φόρτωση του καλαθιού. Δοκιμάστε ξανά.
        </p>

        <div x-cloak x-show="!loading && !failed && lines.length === 0">
            <p class="mb-4">Το καλάθι σας είναι άδειο.</p>
            <a href="{{ route('products.index') }}" class="underline">Δείτε τα προϊόντα</a>
        </div>

        <div x-cloak x-show="!loading && !failed && lines.length > 0" class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">
            <form
                method="POST"
                action="{{ route('checkout.store') }}"
                class="lg:col-span-7 flex flex-col gap-4"
                @submit="$refs.items.value = JSON.stringify($store.cart.items)"
                novalidate
            >
                @csrf
                <input type="hidden" name="items" x-ref="items">

                <h2 class="text-xl font-bold">Στοιχεία αποστολής</h2>

                @foreach ([
                    ['name', 'Ονοματεπώνυμο', 'text', 'name'],
                    ['email', 'Email', 'email', 'email'],
                    ['phone', 'Τηλέφωνο', 'tel', 'tel'],
                    ['address', 'Διεύθυνση', 'text', 'street-address'],
                    ['city', 'Πόλη', 'text', 'address-level2'],
                    ['postal_code', 'Τ.Κ.', 'text', 'postal-code'],
                ] as [$field, $label, $type, $autocomplete])
                    <div class="flex flex-col gap-1">
                        <label for="{{ $field }}" class="font-semibold">{{ $label }}</label>
                        <input
                            id="{{ $field }}"
                            name="{{ $field }}"
                            type="{{ $type }}"
                            autocomplete="{{ $autocomplete }}"
                            value="{{ old($field) }}"
                            required
                            @class(['p-2 border bg-white dark:bg-gray-800', 'border-red-700' => $errors->has($field), 'border-gray-500' => ! $errors->has($field)])
                            @error($field) aria-invalid="true" aria-describedby="{{ $field }}-error" @enderror
                        >
                        @error($field)
                            <p id="{{ $field }}-error" class="text-sm text-red-700">{{ $message }}</p>
                        @enderror
                    </div>
                @endforeach

                <div class="flex flex-col gap-1">
                    <label for="country" class="font-semibold">Χώρα</label>
                    <select id="country" name="country" autocomplete="country" class="p-2 border border-gray-500 bg-white dark:bg-gray-800">
                        @foreach ($countries as $code => $countryName)
                            <option value="{{ $code }}" @selected(old('country', 'GR') === $code)>{{ $countryName }}</option>
                        @endforeach
                    </select>
                    @error('country')
                        <p class="text-sm text-red-700">{{ $message }}</p>
                    @enderror
                </div>

                <div class="flex flex-col gap-1">
                    <label for="notes" class="font-semibold">Σημειώσεις <span class="font-normal text-gray-600">(προαιρετικό)</span></label>
                    <textarea id="notes" name="notes" rows="3" class="p-2 border border-gray-500 bg-white dark:bg-gray-800">{{ old('notes') }}</textarea>
                    @error('notes')
                        <p class="text-sm text-red-700">{{ $message }}</p>
                    @enderror
                </div>

                <button
                    type="submit"
                    class="mt-2 py-3 px-6 bg-black text-white dark:bg-gray-100 dark:text-black uppercase tracking-wider font-bold hover:opacity-80 transition-opacity disabled:opacity-40 disabled:cursor-not-allowed"
                    :disabled="!purchasable"
                >
                    Υποβολή παραγγελίας
                </button>
            </form>

            <aside class="lg:col-span-5 p-4 border border-gray-400">
                <h2 class="text-xl font-bold mb-4">Η παραγγελία σας</h2>

                <ul class="divide-y divide-gray-400">
                    <template x-for="line in lines" :key="line.id">
                        <li class="py-2 flex justify-between gap-4">
                            <span>
                                <span x-text="line.name"></span>
                                <span class="text-gray-600" x-text="'× ' + line.qty"></span>
                                <span x-show="!line.available" class="block text-red-700 text-sm">Μη διαθέσιμο</span>
                            </span>
                            <span class="font-semibold whitespace-nowrap" x-text="line.line_total + ' €'"></span>
                        </li>
                    </template>
                </ul>

                <p class="mt-4 pt-4 border-t border-gray-400 flex justify-between text-lg">
                    <span>Σύνολο</span>
                    <span class="font-bold" x-text="subtotal + ' €'"></span>
                </p>

                <a href="{{ route('cart.index') }}" class="mt-4 inline-block text-sm underline">Επεξεργασία καλαθιού</a>
            </aside>
        </div>
    </section>
</x-layout>
