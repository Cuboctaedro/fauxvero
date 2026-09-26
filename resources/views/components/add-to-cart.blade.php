<div x-data="{ added: false, timer: null }" {{ $attributes->class('flex flex-col gap-2') }}>
    <button
        type="button"
        class="w-full py-3 px-4 bg-black text-white dark:bg-gray-100 dark:text-black uppercase tracking-wider font-bold hover:opacity-80 transition-opacity"
        @click="$store.cart.add({{ $productId }}); added = true; clearTimeout(timer); timer = setTimeout(() => added = false, 2500)"
    >
        Προσθήκη στο καλάθι
    </button>
    <p class="text-sm min-h-5" aria-live="polite">
        <span x-cloak x-show="added">
            Προστέθηκε. <a href="{{ route('cart.index') }}" class="underline">Δείτε το καλάθι</a>
        </span>
    </p>
</div>
