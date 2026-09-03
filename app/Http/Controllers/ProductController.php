<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function index()
    {
        $products = \App\Models\Product::all();

        return view('products.index', compact('products'));
    }

    public function show($slug)
    {
        $product = \App\Models\Product::where('slug', $slug)->firstOrFail();

        return view('products.show', compact('product'));
    }
}
