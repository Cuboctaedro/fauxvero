<?php

use App\Models\Order;
use App\Models\Page;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function translatedLamp(array $overrides = []): Product
{
    return Product::factory()->create([
        'name' => ['el' => 'Λάμπα δαπέδου', 'en' => 'Floor lamp'],
        'slug' => 'floor-lamp',
        'price' => '50.00',
        'in_stock' => true,
        ...$overrides,
    ]);
}

it('redirects the bare root to the default locale', function () {
    // Test requests send "en-us" by default; crawlers usually send nothing.
    $this->get('/', ['Accept-Language' => ''])->assertRedirect('/el');
});

it('redirects the bare root to the browser language when supported', function () {
    $this->get('/', ['Accept-Language' => 'en-US,en;q=0.9'])->assertRedirect('/en');
    $this->get('/', ['Accept-Language' => 'de-DE'])->assertRedirect('/el');
});

it('remembers the last locale visited for the bare root', function () {
    $this->get('/el/products')->assertOk();

    // The visitor's own choice beats the browser language.
    $this->get('/', ['Accept-Language' => 'en'])->assertRedirect('/el');
});

it('permanently redirects unprefixed legacy urls to greek', function (string $from, string $to) {
    $this->get($from)->assertStatus(301)->assertRedirect($to);
})->with([
    'listing' => ['/products', '/el/products'],
    'product' => ['/products/floor-lamp', '/el/products/floor-lamp'],
    'cms page' => ['/about', '/el/about'],
    'query string' => ['/products?page=2', '/el/products?page=2'],
]);

it('returns 404 for unknown pages under a locale instead of redirecting', function () {
    $this->get('/en/nope/nope')->assertNotFound();
    $this->get('/en/nope')->assertNotFound();
});

it('serves product content and ui strings in the url locale', function () {
    $lamp = translatedLamp();

    $this->get('/en/products/'.$lamp->slug)
        ->assertOk()
        ->assertSee('<html lang="en">', false)
        ->assertSee('Floor lamp')
        ->assertSee('Add to cart')
        ->assertDontSee('Λάμπα δαπέδου');

    $this->get('/el/products/'.$lamp->slug)
        ->assertOk()
        ->assertSee('<html lang="el">', false)
        ->assertSee('Λάμπα δαπέδου')
        ->assertSee('Προσθήκη στο καλάθι')
        ->assertDontSee('Floor lamp');
});

it('falls back to the greek content when a translation is missing', function () {
    translatedLamp(['name' => ['el' => 'Μόνο ελληνικά']]);

    $this->get('/en/products')->assertOk()->assertSee('Μόνο ελληνικά');
});

it('serves cms pages in the url locale', function () {
    Page::create([
        'name' => ['el' => 'Σχετικά με εμάς', 'en' => 'About us'],
        'slug' => 'about',
        'status' => 'active',
    ]);

    $this->get('/en/about')->assertOk()->assertSee('About us');
    $this->get('/el/about')->assertOk()->assertSee('Σχετικά με εμάς');
});

it('keeps links inside the current locale', function () {
    $lamp = translatedLamp();

    $this->get('/en/products')
        ->assertSee('href="'.url('/en/products/'.$lamp->slug).'"', false)
        ->assertSee('href="'.url('/en/cart').'"', false);
});

it('links the switcher and hreflang tags to the same page in each locale', function () {
    $lamp = translatedLamp();

    $this->get('/en/products/'.$lamp->slug)
        ->assertSee('href="'.url('/el/products/'.$lamp->slug).'"', false)
        ->assertSee('<link rel="alternate" hreflang="el" href="'.url('/el/products/'.$lamp->slug).'">', false)
        ->assertSee('<link rel="alternate" hreflang="en" href="'.url('/en/products/'.$lamp->slug).'">', false)
        ->assertSee('<link rel="alternate" hreflang="x-default" href="'.url('/').'">', false);
});

it('prices cart lines with names and links in the request locale', function () {
    $lamp = translatedLamp();

    $this->postJson('/en/cart/lines', ['items' => [['id' => $lamp->id, 'qty' => 1]]])
        ->assertOk()
        ->assertJsonPath('lines.0.name', 'Floor lamp')
        ->assertJsonPath('lines.0.url', url('/en/products/'.$lamp->slug));
});

it('returns cart line validation errors as json under any locale', function () {
    $this->postJson('/en/cart/lines', ['items' => 'nope'])->assertUnprocessable();
    $this->post('/en/cart/lines', ['items' => 'nope'])->assertUnprocessable();
});

it('records the checkout locale and redirects within it', function () {
    $lamp = translatedLamp();

    $this->post('/en/checkout', [
        'name' => 'Maria P',
        'email' => 'maria@example.com',
        'phone' => '6900000000',
        'address' => 'Ermou 1',
        'city' => 'Athens',
        'postal_code' => '10563',
        'country' => 'GR',
        'items' => json_encode([['id' => $lamp->id, 'qty' => 1]]),
    ])->assertRedirect('/en/checkout/thank-you');

    expect(Order::with('items')->sole())
        ->locale->toBe('en')
        ->items->first()->product_name->toBe('Floor lamp');
});

it('translates checkout validation messages', function () {
    $this->post('/en/checkout', ['items' => '[]'])
        ->assertSessionHasErrors(['name' => 'The Full name field is required.']);

    $this->post('/el/checkout', ['items' => '[]'])
        ->assertSessionHasErrors(['name' => 'Το πεδίο «Ονοματεπώνυμο» είναι υποχρεωτικό.']);
});
