<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreOrderRequest;
use App\Models\Order;
use App\Support\Cart;
use Illuminate\Support\Facades\DB;

class CheckoutController extends Controller
{
    public function index()
    {
        return view('checkout.index', ['countries' => StoreOrderRequest::COUNTRIES]);
    }

    public function store(StoreOrderRequest $request)
    {
        $cart = Cart::fromItems($request->validated('items'));

        if (! $cart->isPurchasable()) {
            return back()->withInput()->withErrors([
                'items' => 'Κάποια προϊόντα του καλαθιού δεν είναι πλέον διαθέσιμα. Ελέγξτε το καλάθι σας.',
            ]);
        }

        $order = DB::transaction(function () use ($request, $cart) {
            $subtotal = Cart::format($cart->subtotalCents());

            $order = Order::create([
                ...$request->safe()->except('items'),
                'subtotal' => $subtotal,
                'total' => $subtotal,
                'locale' => app()->getLocale(),
            ]);

            $order->items()->createMany($cart->lines->map(fn (array $line) => [
                'product_id' => $line['product']->id,
                'product_name' => $line['product']->name,
                'unit_price' => Cart::format($line['unit_cents']),
                'quantity' => $line['quantity'],
                'line_total' => Cart::format($line['line_cents']),
            ]));

            return $order;
        });

        // Once a payment provider is chosen, redirect to it here instead.
        $request->session()->put('checkout.order_id', $order->id);

        return redirect()->route('checkout.thank-you')->with('checkout.placed', true);
    }

    public function thankYou()
    {
        $order = Order::with('items')->find(session('checkout.order_id'));

        abort_if($order === null, 404);

        return view('checkout.thank-you', compact('order'));
    }
}
