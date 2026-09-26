<?php

use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('renders the cart page instead of treating it as a cms page', function () {
    $this->get('/cart')->assertOk()->assertSee('Καλάθι');
});

it('prices cart lines from the database', function () {
    $lamp = Product::factory()->create(['price' => '19.99', 'in_stock' => true]);
    $chair = Product::factory()->create(['price' => '120.00', 'in_stock' => true]);

    $this->postJson('/cart/lines', ['items' => [
        ['id' => $lamp->id, 'qty' => 3],
        ['id' => $chair->id, 'qty' => 1],
    ]])
        ->assertOk()
        ->assertJsonPath('lines.0.id', $lamp->id)
        ->assertJsonPath('lines.0.unit_price', '19.99')
        ->assertJsonPath('lines.0.line_total', '59.97')
        ->assertJsonPath('lines.1.line_total', '120.00')
        ->assertJsonPath('subtotal', '179.97')
        ->assertJsonPath('purchasable', true)
        ->assertJsonPath('missing_ids', []);
});

it('merges duplicate lines for the same product', function () {
    $product = Product::factory()->create(['price' => '10.00', 'in_stock' => true]);

    $this->postJson('/cart/lines', ['items' => [
        ['id' => $product->id, 'qty' => 1],
        ['id' => $product->id, 'qty' => 2],
    ]])
        ->assertJsonCount(1, 'lines')
        ->assertJsonPath('lines.0.qty', 3)
        ->assertJsonPath('subtotal', '30.00');
});

it('reports unknown and inactive products as missing', function () {
    $active = Product::factory()->create(['in_stock' => true]);
    $inactive = Product::factory()->create(['status' => 'inactive']);

    $this->postJson('/cart/lines', ['items' => [
        ['id' => $active->id, 'qty' => 1],
        ['id' => $inactive->id, 'qty' => 1],
        ['id' => 9999, 'qty' => 1],
    ]])
        ->assertJsonCount(1, 'lines')
        ->assertJsonPath('missing_ids', [$inactive->id, 9999])
        ->assertJsonPath('purchasable', false);
});

it('flags out of stock products as unavailable', function () {
    $product = Product::factory()->create(['in_stock' => false]);

    $this->postJson('/cart/lines', ['items' => [['id' => $product->id, 'qty' => 1]]])
        ->assertJsonPath('lines.0.available', false)
        ->assertJsonPath('purchasable', false);
});

it('accepts an empty cart', function () {
    $this->postJson('/cart/lines', ['items' => []])
        ->assertOk()
        ->assertJsonPath('lines', [])
        ->assertJsonPath('subtotal', '0.00')
        ->assertJsonPath('purchasable', false);
});

it('rejects invalid quantities', function (int $qty) {
    $product = Product::factory()->create();

    $this->postJson('/cart/lines', ['items' => [['id' => $product->id, 'qty' => $qty]]])
        ->assertUnprocessable()
        ->assertJsonValidationErrors('items.0.qty');
})->with([0, -1, 100]);
