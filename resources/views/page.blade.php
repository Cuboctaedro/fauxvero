<x-layout :meta-title="$page->name">

    <article class="max-w-3xl">
        <h1 class="text-3xl font-bold mb-6">{{ $page->name }}</h1>

        <div class="content text-lg">
            @if ($page->template === 'blocks')
                <x-content-blocks :blocks="$page->content" />
            @elseif (filled($page->description))
                {{ \Filament\Forms\Components\RichEditor\RichContentRenderer::make($page->description) }}
            @endif
        </div>
    </article>

</x-layout>
