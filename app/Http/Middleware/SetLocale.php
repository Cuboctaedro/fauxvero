<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\URL;
use Symfony\Component\HttpFoundation\Response;

/**
 * Applies the {locale} route prefix to the storefront: sets the app locale,
 * remembers it for the bare "/" redirect, and makes route() fill in the current
 * locale so views can keep calling route('products.index') without passing it.
 */
class SetLocale
{
    public const SESSION_KEY = 'locale';

    public function handle(Request $request, Closure $next): Response
    {
        $locale = $request->route('locale');

        app()->setLocale($locale);
        URL::defaults(['locale' => $locale]);
        $request->session()->put(self::SESSION_KEY, $locale);

        // Controllers receive route parameters by position, so drop the prefix
        // to keep signatures like show($slug) unaware of it.
        $request->route()->forgetParameter('locale');

        return $next($request);
    }

    /**
     * @return array<int, string>
     */
    public static function supported(): array
    {
        return array_keys(config('app.locales'));
    }

    /**
     * The locale to send a visitor to when the URL doesn't name one.
     */
    public static function preferred(Request $request): string
    {
        $remembered = $request->hasSession() ? $request->session()->get(self::SESSION_KEY) : null;

        if (in_array($remembered, self::supported(), true)) {
            return $remembered;
        }

        return $request->getPreferredLanguage(self::supported()) ?? config('app.locale');
    }
}
