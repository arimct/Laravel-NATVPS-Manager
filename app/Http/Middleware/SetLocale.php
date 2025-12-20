<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Session;
use Symfony\Component\HttpFoundation\Response;

class SetLocale
{
    /**
     * Supported locales
     */
    protected array $supportedLocales = ['en', 'id'];

    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Priority: Session > User preference > Browser > Default
        $locale = Session::get('locale');
        
        if (!$locale && auth()->check() && auth()->user()->locale) {
            $locale = auth()->user()->locale;
        }
        
        if (!$locale) {
            $locale = $this->getBrowserLocale($request);
        }
        
        if (!$locale || !in_array($locale, $this->supportedLocales)) {
            $locale = config('app.locale', 'en');
        }
        
        App::setLocale($locale);
        
        return $next($request);
    }

    /**
     * Get locale from browser Accept-Language header
     */
    protected function getBrowserLocale(Request $request): ?string
    {
        $acceptLanguage = $request->header('Accept-Language');
        
        if (!$acceptLanguage) {
            return null;
        }

        $languages = explode(',', $acceptLanguage);
        
        foreach ($languages as $language) {
            $lang = strtolower(substr(trim(explode(';', $language)[0]), 0, 2));
            
            if (in_array($lang, $this->supportedLocales)) {
                return $lang;
            }
        }
        
        return null;
    }
}
