<nav class="flex items-center justify-between mb-6 p-4 gap-4 w-full">
    <a href="{{ route('homepage') }}">
        <img src="{{ asset('logos/faux-vero-logo-horizontal.svg') }}" alt="Faux Vero Logo" class="h-10">
        <span class="sr-only">Faux Vero</span>
    </a>
    <div class="flex-1 flex items-center justify-end gap-4">
        <ul class="flex flex-col md:flex-row gap-4 justify-start items-start uppercase tracking-wider lg:col-span-2 xl:col-span-3 md:border-r md:pr-4">
            <li>
                <a href="{{ route('products.index') }}" class="pb-1 pl-[2px] border-b-2 border-transparent hover:border-gray-800">{{ __('Products') }}</a>
            </li>
            <li><a href="{{ route('page.show', ['slug' => 'about']) }}" class="pb-1 pl-[2px] border-b-2 border-transparent hover:border-gray-800">{{ __('About') }}</a></li>
            <li><a href="{{ route('page.show', ['slug' => 'contact']) }}" class="pb-1 pl-[2px] border-b-2 border-transparent hover:border-gray-800">{{ __('Contact') }}</a></li>
        </ul>
        <a href="{{ route('cart.index') }}" x-data class="justify-self-end flex items-center gap-2 pb-1 border-b-2 border-transparent hover:border-gray-800 uppercase tracking-wider">
            {{ __('Cart') }}
            <span
                x-cloak
                x-show="$store.cart.count > 0"
                x-text="$store.cart.count"
                class="min-w-6 h-6 px-1 flex items-center justify-center rounded-full bg-black text-white dark:bg-gray-100 dark:text-black text-sm"
            ></span>
        </a>
        <x-language-switcher class="md:border-l md:pl-4" />
    </div>
</nav>
