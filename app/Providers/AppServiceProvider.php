<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Spatie\Translatable\Facades\Translatable;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Content is written in Greek first: on another locale, show the Greek
        // text (or any filled-in translation) rather than an empty field.
        // The admin forms read translations without fallback, so they're unaffected.
        Translatable::fallback(fallbackLocale: config('app.locale'), fallbackAny: true);
    }
}
