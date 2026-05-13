<?php

namespace App\Http\Middleware;

use Carbon\Carbon;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Symfony\Component\HttpFoundation\Response;

class SetLocale
{
    private const SUPPORTED = ['pt', 'en', 'fr'];

    private const CARBON_LOCALES = [
        'pt' => 'pt_BR',
        'en' => 'en',
        'fr' => 'fr',
    ];

    public function handle(Request $request, Closure $next): Response
    {
        $locale = session('locale', config('app.locale', 'pt'));

        if (!in_array($locale, self::SUPPORTED)) {
            $locale = 'pt';
        }

        App::setLocale($locale);
        Carbon::setLocale(self::CARBON_LOCALES[$locale]);

        return $next($request);
    }
}
