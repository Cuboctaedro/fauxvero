<x-layout meta-title="Ευχαριστούμε | Fauxvero" :meta-robots="false" page-path="/checkout/thank-you">
    <section class="max-w-2xl" @if (session('checkout.placed')) x-data x-init="$store.cart.clear()" @endif>
        <h1 class="text-3xl font-bold mb-2">Ευχαριστούμε για την παραγγελία σας</h1>
        <p class="mb-6">
            Αριθμός παραγγελίας: <strong>{{ $order->number }}</strong>.
            Θα επικοινωνήσουμε μαζί σας στο {{ $order->email }}.
        </p>

        <ul class="divide-y divide-gray-400 border-y border-gray-400">
            @foreach ($order->items as $item)
                <li class="py-2 flex justify-between gap-4">
                    <span>{{ $item->product_name }} <span class="text-gray-600">× {{ $item->quantity }}</span></span>
                    <span class="font-semibold whitespace-nowrap">{{ $item->line_total }} €</span>
                </li>
            @endforeach
        </ul>

        <p class="mt-4 flex justify-between text-lg">
            <span>Σύνολο</span>
            <span class="font-bold">{{ $order->total }} €</span>
        </p>

        <a href="{{ route('products.index') }}" class="mt-8 inline-block underline">Συνέχεια αγορών</a>
    </section>
</x-layout>
