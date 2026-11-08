<x-layout :meta-title="__('Thank you').' | Fauxvero'" :meta-robots="false">
    <section class="max-w-2xl" @if (session('checkout.placed')) x-data x-init="$store.cart.clear()" @endif>
        <h1 class="text-3xl font-bold mb-2">{{ __('Thank you for your order') }}</h1>
        <p class="mb-6">
            {{ __('Order number') }}: <strong>{{ $order->number }}</strong>.
            {{ __('We will contact you at :email.', ['email' => $order->email]) }}
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
            <span>{{ __('Total') }}</span>
            <span class="font-bold">{{ $order->total }} €</span>
        </p>

        <a href="{{ route('products.index') }}" class="mt-8 inline-block underline">{{ __('Continue shopping') }}</a>
    </section>
</x-layout>
