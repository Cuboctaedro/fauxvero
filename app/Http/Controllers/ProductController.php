<?php

namespace App\Http\Controllers;

use App\Models\Product;

class ProductController extends Controller
{
    public function index()
    {
        $products = Product::active()->with('featuredAsset.media')->get();

        return view('products.index', compact('products'));
    }

    public function show($slug)
    {
        $product = Product::active()->with('featuredAsset.media', 'galleryAssets.media')
            ->where('slug', $slug)->firstOrFail();

        return view('products.show', compact('product'));
    }
}
