<nav
    x-data="{ scrolled: window.scrollY > 0 }"
    x-on:scroll.window.passive="scrolled = window.scrollY > 0"
    :class="{ 'is-scrolled shadow-md': scrolled }"
    class="fixed top-0 right-0 left-0 flex items-center justify-between gap-4 w-full bg-white h-10 p-1"
>
    <x-main-menu />

    <x-logo />

    <div class="flex-1 flex items-center justify-end gap-4">
        <x-cart-link />
        <x-language-switcher class="md:border-l md:pl-4" />
    </div>
</nav>
