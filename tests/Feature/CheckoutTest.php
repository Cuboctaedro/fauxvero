<?php

use App\Enums\OrderStatus;
use App\Filament\Resources\Orders\Pages\EditOrder;
use App\Filament\Resources\Orders\Pages\ListOrders;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

function checkoutDetails(array $overrides = []): array
{
    return [
        'name' => 'Μαρία Παπαδοπούλου',
        'email' => 'maria@example.com',
        'phone' => '6900000000',
        'address' => 'Ερμού 1',
        'city' => 'Αθήνα',
        'postal_code' => '10563',
        'country' => 'GR',
        'notes' => null,
        ...$overrides,
    ];
}

it('renders the checkout page', function () {
    $this->get('/checkout')->assertOk()->assertSee('Ταμείο');
});

it('creates a pending order priced from the database', function () {
    $lamp = Product::factory()->create(['name' => 'Lamp', 'price' => '19.99', 'in_stock' => true]);
    $chair = Product::factory()->create(['name' => 'Chair', 'price' => '120.00', 'in_stock' => true]);

    // Extra keys like a client-side price are ignored; only id and qty are used.
    $items = json_encode([
        ['id' => $lamp->id, 'qty' => 2, 'price' => '0.01'],
        ['id' => $chair->id, 'qty' => 1],
    ]);

    $this->post('/checkout', checkoutDetails(['items' => $items, 'subtotal' => '1.00']))
        ->assertRedirect(route('checkout.thank-you'));

    $order = Order::with('items')->sole();

    expect($order->status)->toBe(OrderStatus::Pending)
        ->and($order->number)->toBe('FV-'.str_pad((string) $order->id, 6, '0', STR_PAD_LEFT))
        ->and($order->subtotal)->toBe('159.98')
        ->and($order->total)->toBe('159.98')
        ->and($order->email)->toBe('maria@example.com')
        ->and($order->locale)->toBe(app()->getLocale())
        ->and($order->items)->toHaveCount(2);

    expect($order->items->firstWhere('product_id', $lamp->id))
        ->product_name->toBe('Lamp')
        ->unit_price->toBe('19.99')
        ->quantity->toBe(2)
        ->line_total->toBe('39.98');
});

it('shows the thank you page once the order is placed', function () {
    $product = Product::factory()->create(['in_stock' => true]);

    $this->followingRedirects()
        ->post('/checkout', checkoutDetails(['items' => json_encode([['id' => $product->id, 'qty' => 1]])]))
        ->assertOk()
        ->assertSee(Order::sole()->number)
        ->assertSee('$store.cart.clear()', false);
});

it('does not show the thank you page without a placed order', function () {
    Order::factory()->create();

    $this->get('/checkout/thank-you')->assertNotFound();
});

it('refuses out of stock or inactive products', function (array $attributes) {
    $product = Product::factory()->create($attributes);

    $this->from('/checkout')
        ->post('/checkout', checkoutDetails(['items' => json_encode([['id' => $product->id, 'qty' => 1]])]))
        ->assertRedirect('/checkout')
        ->assertSessionHasErrors('items');

    expect(Order::count())->toBe(0);
})->with([
    'out of stock' => [['in_stock' => false]],
    'inactive' => [['in_stock' => true, 'status' => 'inactive']],
]);

it('refuses a product that no longer exists', function () {
    $this->post('/checkout', checkoutDetails(['items' => json_encode([['id' => 9999, 'qty' => 1]])]))
        ->assertSessionHasErrors('items');

    expect(Order::count())->toBe(0);
});

it('refuses an empty cart', function (?string $items) {
    $this->post('/checkout', checkoutDetails(['items' => $items]))
        ->assertSessionHasErrors('items');
})->with([
    'empty list' => ['[]'],
    'missing' => [null],
    'not json' => ['nope'],
]);

it('validates the customer details', function () {
    $product = Product::factory()->create(['in_stock' => true]);

    $this->post('/checkout', checkoutDetails([
        'name' => '',
        'email' => 'not-an-email',
        'country' => 'US',
        'items' => json_encode([['id' => $product->id, 'qty' => 1]]),
    ]))->assertSessionHasErrors(['name', 'email', 'country']);

    expect(Order::count())->toBe(0);
});

it('lists orders in the admin panel and lets the status be changed', function () {
    $this->actingAs(User::factory()->create());

    $order = Order::factory()->create();
    OrderItem::factory()->for($order)->create(['product_name' => 'Lamp']);

    Livewire::test(ListOrders::class)
        ->assertOk()
        ->assertCanSeeTableRecords([$order]);

    Livewire::test(EditOrder::class, ['record' => $order->getRouteKey()])
        ->assertOk()
        ->assertSee('Lamp')
        ->fillForm(['status' => OrderStatus::Shipped])
        ->call('save')
        ->assertHasNoFormErrors();

    expect($order->fresh()->status)->toBe(OrderStatus::Shipped);
});
