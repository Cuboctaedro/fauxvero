<?php

namespace App\Http\Controllers;

use App\Support\Cart;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CartController extends Controller
{
    public function index()
    {
        return view('cart.index');
    }

    /**
     * Prices the browser's cart ({id, qty} pairs) for display.
     */
    public function lines(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'items' => ['present', 'array'],
            'items.*.id' => ['required', 'integer'],
            'items.*.qty' => ['required', 'integer', 'min:1', 'max:'.Cart::MAX_QUANTITY],
        ]);

        return response()->json(Cart::fromItems($validated['items'])->toArray());
    }
}
