@props(['blocks' => []])

@php
    // One query for every image the blocks reference.
    $assetIds = collect($blocks ?? [])->flatMap(fn (array $block): array => match ($block['type'] ?? null) {
        'image' => [$block['data']['asset_id'] ?? null],
        'gallery' => array_column($block['data']['images'] ?? [], 'asset_id'),
        default => [],
    })->filter()->unique();

    $assets = \App\Models\Asset::with('media')->findMany($assetIds)->keyBy('id');
@endphp

@foreach ($blocks ?? [] as $block)
    @php($data = $block['data'] ?? [])

    @switch($block['type'] ?? null)
        @case('heading')
            @php($level = in_array($data['level'] ?? null, ['h2', 'h3', 'h4', 'h5', 'h6'], true) ? $data['level'] : 'h2')
            <{{ $level }}>{{ $data['content'] ?? '' }}</{{ $level }}>
            @break

        @case('paragraph')
            @if (filled($data['content'] ?? null))
                {{ \Filament\Forms\Components\RichEditor\RichContentRenderer::make($data['content']) }}
            @endif
            @break

        @case('image')
            @if ($asset = $assets->get($data['asset_id'] ?? null))
                <figure>
                    <x-asset-image
                        :asset="$asset"
                        sizes="(min-width: 800px) 768px, 100vw"
                        class="w-full h-auto"
                    />
                </figure>
            @endif
            @break

        @case('gallery')
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                @foreach ($data['images'] ?? [] as $image)
                    <x-asset-image
                        :asset="$assets->get($image['asset_id'] ?? null)"
                        sizes="(min-width: 800px) 376px, (min-width: 640px) 50vw, 100vw"
                        class="w-full h-auto object-cover"
                    />
                @endforeach
            </div>
            @break

        @case('list')
            @php($tag = ($data['type'] ?? 'ul') === 'ol' ? 'ol' : 'ul')
            <{{ $tag }}>
                @foreach ($data['content'] ?? [] as $item)
                    <li>{{ $item['content'] ?? '' }}</li>
                @endforeach
            </{{ $tag }}>
            @break
    @endswitch
@endforeach
