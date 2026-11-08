<button
    type="button"
    data-disclosure
    data-disclosure-open-class="bg-white"
    data-label-closed="{{ __('Open menu') }}"
    data-label-open="{{ __('Close menu') }}"
    aria-expanded="false"
    aria-controls="menu-list"
    aria-label="{{ __('Open menu') }}"
    class="w-8 h-8 flex items-center justify-center focus:outline-2 focus:outline-offset-1 focus:outline-gray-300 lg:hidden relative z-50 cursor-pointer"
>
    <span class="block w-6 h-6" aria-hidden="true">
        <svg xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 24 24">
            <path fill-rule="evenodd" d="M3 6.75A.75.75 0 0 1 3.75 6h16.5a.75.75 0 0 1 0 1.5H3.75A.75.75 0 0 1 3 6.75M3 12a.75.75 0 0 1 .75-.75h16.5a.75.75 0 0 1 0 1.5H3.75A.75.75 0 0 1 3 12m0 5.25a.75.75 0 0 1 .75-.75h16.5a.75.75 0 0 1 0 1.5H3.75a.75.75 0 0 1-.75-.75" clip-rule="evenodd" />
        </svg>
    </span>
    <span class="sr-only">Menu</span>
</button>

<ul
    id="menu-list"
    class="hidden absolute left-0 top-8 mt-2 min-w-40 flex flex-col gap-3 p-4 uppercase tracking-wider bg-white dark:bg-gray-900 shadow-lg z-50 lg:static lg:flex lg:flex-row lg:gap-6 lg:p-0 lg:bg-transparent lg:shadow-none lg:pl-4"
>
    <li>
        <a
            href="{{ route('products.index') }}"
            class="pb-1 pl-[2px] border-b-2 border-transparent hover:border-gray-800"
        >
            {{ __('Products') }}
        </a>
    </li>
    <li>
        <a
            href="{{ route('page.show', ['slug' => 'about']) }}"
            class="pb-1 pl-[2px] border-b-2 border-transparent hover:border-gray-800"
        >
            {{ __('About') }}
        </a>
    </li>
    <li>
        <a
            href="{{ route('page.show', ['slug' => 'contact']) }}"
            class="pb-1 pl-[2px] border-b-2 border-transparent hover:border-gray-800"
        >
            {{ __('Contact') }}
        </a>
    </li>
</ul>
