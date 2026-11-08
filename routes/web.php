<?php

use App\Http\Controllers\CartController;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\HomepageController;
use App\Http\Controllers\PageController;
use App\Http\Controllers\ProductController;
use App\Http\Middleware\SetLocale;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/', fn (Request $request) => redirect('/'.SetLocale::preferred($request)));

Route::prefix('{locale}')
    ->whereIn('locale', SetLocale::supported())
    ->middleware(SetLocale::class)
    ->group(function () {
        Route::get('/', [HomepageController::class, 'index'])->name('homepage');
        Route::get('/products', [ProductController::class, 'index'])->name('products.index');
        Route::get('/products/{slug}', [ProductController::class, 'show'])->name('products.show');

        Route::get('/cart', [CartController::class, 'index'])->name('cart.index');
        Route::post('/cart/lines', [CartController::class, 'lines'])->name('cart.lines');

        Route::get('/checkout', [CheckoutController::class, 'index'])->name('checkout.index');
        Route::post('/checkout', [CheckoutController::class, 'store'])->name('checkout.store');
        Route::get('/checkout/thank-you', [CheckoutController::class, 'thankYou'])->name('checkout.thank-you');

        // Catch-all for CMS pages: keep it last.
        Route::get('/{slug}', [PageController::class, 'show'])->name('page.show');
    });

// URLs from before the locale prefix existed were all Greek: send them to /el/...
Route::fallback(function (Request $request) {
    abort_if(in_array($request->segment(1), SetLocale::supported(), true), 404);

    $query = $request->getQueryString();

    return redirect(
        '/'.config('app.locale').'/'.$request->path().($query ? '?'.$query : ''),
        301,
    );
});
