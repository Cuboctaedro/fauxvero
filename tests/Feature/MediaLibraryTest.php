<?php

use App\Filament\Resources\Assets\Pages\CreateAsset;
use App\Filament\Resources\Assets\Pages\ListAssets;
use App\Filament\Resources\Products\Pages\CreateProduct;
use App\Filament\Resources\Products\Pages\EditProduct;
use App\Models\Asset;
use App\Models\Page;
use App\Models\Product;
use App\Models\User;
use Filament\Actions\Testing\TestAction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;

uses(RefreshDatabase::class);

beforeEach(function () {
    Storage::fake('public');
    $this->actingAs(User::factory()->create());
});

function libraryAsset(string $name = 'photo'): Asset
{
    $asset = Asset::create(['name' => $name]);
    $asset->addMedia(UploadedFile::fake()->image($name.'.jpg'))->toMediaCollection(Asset::COLLECTION);

    return $asset;
}

it('lets one asset be used by several records', function () {
    $asset = libraryAsset();
    $product = Product::factory()->create(['featured_asset_id' => $asset->id]);
    $page = Page::create(['name' => 'About', 'slug' => 'about', 'featured_asset_id' => $asset->id]);
    $product->galleryItems()->create(['asset_id' => $asset->id]);

    expect($product->featuredAsset->is($asset))->toBeTrue()
        ->and($page->featuredAsset->is($asset))->toBeTrue()
        ->and($product->featuredImage)->toBe($asset->url('web'))
        ->and($product->galleryAssets->pluck('id')->all())->toBe([$asset->id]);
});

it('detaches an asset everywhere when it is deleted', function () {
    $asset = libraryAsset();
    $product = Product::factory()->create(['featured_asset_id' => $asset->id]);
    $product->galleryItems()->create(['asset_id' => $asset->id]);

    $asset->delete();

    expect($product->fresh()->featured_asset_id)->toBeNull()
        ->and($product->galleryItems()->count())->toBe(0);
});

it('picks featured and gallery images from the library', function () {
    [$a, $b, $c] = [libraryAsset('a'), libraryAsset('b'), libraryAsset('c')];
    $product = Product::factory()->create();

    Livewire::test(EditProduct::class, ['record' => $product->id])
        ->fillForm([
            'featured_asset_id' => $a->id,
            'galleryItems' => [['asset_id' => $c->id], ['asset_id' => $b->id]],
        ])
        ->call('save')
        ->assertHasNoFormErrors();

    $product->refresh();

    expect($product->featured_asset_id)->toBe($a->id)
        ->and($product->galleryAssets->pluck('id')->all())->toBe([$c->id, $b->id]);
});

it('rejects the same image twice in a gallery', function () {
    $asset = libraryAsset();

    Livewire::test(EditProduct::class, ['record' => Product::factory()->create()->id])
        ->fillForm(['galleryItems' => [['asset_id' => $asset->id], ['asset_id' => $asset->id]]])
        ->call('save')
        ->assertHasFormErrors();
});

it('uploads a new asset from the picker with alt text in the active locale', function () {
    Livewire::test(CreateProduct::class)
        ->set('activeLocale', 'el')
        ->callAction(TestAction::make('createOption')->schemaComponent('featured_asset_id'), data: [
            'file' => [UploadedFile::fake()->image('new.jpg')],
            'name' => 'New photo',
            'alt' => 'Φωτογραφία',
        ])
        ->assertHasNoFormErrors();

    $asset = Asset::sole();

    expect($asset->name)->toBe('New photo')
        ->and($asset->getTranslation('alt', 'el', false))->toBe('Φωτογραφία')
        ->and($asset->getFirstMedia(Asset::COLLECTION))->not->toBeNull();
});

it('edits alt text from the picker without touching other locales', function () {
    $asset = libraryAsset();
    $asset->setTranslation('alt', 'el', 'Ελληνικά')->save();
    $product = Product::factory()->create(['featured_asset_id' => $asset->id]);

    Livewire::test(EditProduct::class, ['record' => $product->id])
        ->set('activeLocale', 'en')
        ->callAction(TestAction::make('editOption')->schemaComponent('featured_asset_id'), data: [
            'name' => 'Renamed',
            'alt' => 'English',
        ])
        ->assertHasNoFormErrors();

    $asset->refresh();

    expect($asset->name)->toBe('Renamed')
        ->and($asset->getTranslation('alt', 'en', false))->toBe('English')
        ->and($asset->getTranslation('alt', 'el', false))->toBe('Ελληνικά');
});

it('lists and uploads images in the media resource', function () {
    $existing = libraryAsset();

    Livewire::test(ListAssets::class)->assertCanSeeTableRecords([$existing]);

    Livewire::test(CreateAsset::class)
        ->fillForm(['file' => [UploadedFile::fake()->image('x.png')], 'name' => 'Uploaded'])
        ->call('create')
        ->assertHasNoFormErrors();

    expect(Asset::where('name', 'Uploaded')->first()->getFirstMedia(Asset::COLLECTION))->not->toBeNull();
});

it('lets users into the admin panel', function () {
    $this->get('/admin/media')->assertOk();
});
