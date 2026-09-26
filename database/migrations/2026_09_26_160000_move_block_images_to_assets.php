<?php

use App\Models\Asset;
use App\Models\Page;
use Illuminate\Database\Migrations\Migration;

/**
 * Image and gallery content blocks used to store an uploaded file path and alt
 * text. They now reference library Assets, so each stored file becomes an Asset
 * and the block keeps only its id.
 */
return new class extends Migration
{
    public function up(): void
    {
        Page::all()->each(function (Page $page): void {
            $translations = $page->getTranslations('content');

            foreach ($translations as $locale => $blocks) {
                $translations[$locale] = collect($blocks ?? [])
                    ->map(fn (array $block): array => $this->migrateBlock($block, $locale))
                    ->all();
            }

            $page->setTranslations('content', $translations)->saveQuietly();
        });
    }

    protected function migrateBlock(array $block, string $locale): array
    {
        $data = $block['data'] ?? [];

        if (($block['type'] ?? null) === 'image' && isset($data['url'])) {
            $block['data'] = ['asset_id' => $this->toAsset($data, $locale)];
        }

        if (($block['type'] ?? null) === 'gallery') {
            $block['data']['images'] = collect($data['images'] ?? [])
                ->map(fn (array $image): array => isset($image['url'])
                    ? ['asset_id' => $this->toAsset($image, $locale)]
                    : $image)
                ->filter(fn (array $image): bool => filled($image['asset_id'] ?? null))
                ->values()
                ->all();
        }

        return $block;
    }

    protected function toAsset(array $image, string $locale): ?int
    {
        $path = collect([
            storage_path('app/public/'.$image['url']),
            storage_path('app/private/'.$image['url']),
        ])->first(fn (string $path): bool => is_file($path));

        if (! $path) {
            return null;
        }

        $asset = new Asset(['name' => pathinfo($image['url'], PATHINFO_FILENAME)]);
        $asset->setTranslation('alt', $locale, (string) ($image['alt'] ?? ''));
        $asset->save();

        $asset->addMedia($path)->preservingOriginal()->toMediaCollection(Asset::COLLECTION);

        return $asset->getKey();
    }
};
