<?php

use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('hides inactive products from the listing', function () {
    Product::factory()->create(['name' => 'Visible lamp']);
    Product::factory()->create(['name' => 'Hidden chair', 'status' => 'inactive']);

    $this->get('/products')
        ->assertOk()
        ->assertSee('Visible lamp')
        ->assertDontSee('Hidden chair');
});

it('returns 404 for an inactive product page', function () {
    $product = Product::factory()->create(['status' => 'inactive']);

    $this->get('/products/'.$product->slug)->assertNotFound();
});

it('shows the add to cart button for products in stock', function () {
    $product = Product::factory()->create(['in_stock' => true]);

    $this->get('/products/'.$product->slug)
        ->assertOk()
        ->assertSee('Προσθήκη στο καλάθι')
        ->assertDontSee('Εξαντλημένο');
});

it('shows an out of stock label instead of the button', function () {
    $product = Product::factory()->create(['in_stock' => false]);

    $this->get('/products/'.$product->slug)
        ->assertOk()
        ->assertSee('Εξαντλημένο')
        ->assertDontSee('Προσθήκη στο καλάθι');
});

it('labels out of stock products in the listing', function () {
    Product::factory()->create(['in_stock' => true]);

    $this->get('/products')->assertDontSee('Εξαντλημένο');

    Product::factory()->create(['in_stock' => false]);

    $this->get('/products')->assertSee('Εξαντλημένο');
});

it('lists products without a type or price', function () {
    Product::factory()->create(['name' => 'Bare lamp', 'type' => null, 'price' => null]);

    $this->get('/products')->assertOk()->assertSee('Bare lamp');
});
