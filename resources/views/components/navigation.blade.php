<nav class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 gap-4 items-start mb-6">
    <a href="{{ route('homepage') }}" class="text-teal-500">
        <img src="{{ asset('logos/faux-vero-logo.svg') }}" alt="Faux Vero Logo" class="h-36">
        <span class="sr-only">Faux Vero</span>
    </a>
    <ul class="flex flex-col md:flex-row gap-4 justify-start items-start uppercase tracking-wider lg:col-span-2 xl:col-span-3">
        <li><a href="{{ route('products.index') }}" class="pb-1 border-b-2 border-transparent hover:border-gray-800">Προϊόντα</a></li>
        <li><a href="{{ route('page.show', ['slug' => 'about']) }}" class="pb-1 border-b-2 border-transparent hover:border-gray-800">Σχετικά</a></li>
        <li><a href="{{ route('page.show', ['slug' => 'contact']) }}" class="pb-1 border-b-2 border-transparent hover:border-gray-800">Επικοινωνία</a></li>
    </ul>
    <div class="justify-self-end">Καλάθι</div>
</nav>