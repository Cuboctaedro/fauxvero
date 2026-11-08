<?php

namespace App\Support;

use Illuminate\Support\Facades\Request;

class LocalizedUrl
{
    /**
     * The current page's URL in another locale, falling back to that locale's
     * homepage when the current route isn't a localized one.
     */
    public static function current(string $locale): string
    {
        $route = Request::route();

        if (! $route?->getName() || ! in_array('locale', $route->parameterNames(), true)) {
            return route('homepage', ['locale' => $locale]);
        }

        $url = route($route->getName(), [...$route->parameters(), 'locale' => $locale]);
        $query = Request::getQueryString();

        return $query ? $url.'?'.$query : $url;
    }

    /**
     * @return array<string, string> locale code => URL of the current page in it
     */
    public static function alternates(): array
    {
        return collect(config('app.locales'))
            ->mapWithKeys(fn (string $name, string $code) => [$code => self::current($code)])
            ->all();
    }
}
