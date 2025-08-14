<?php

namespace TronderData\TdCostCalcultaror\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config;

class SetLocale
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        // Check if locale is in request (highest priority)
        if ($request->has('locale')) {
            $locale = $request->input('locale');
            
            // Validate locale against available languages
            $availableLocales = array_keys(Config::get('td-cost-calcultaror.languages.available', ['en' => 'English']));
            
            if (in_array($locale, $availableLocales)) {
                App::setLocale($locale);
                session(['locale' => $locale]);
            }
        } 
        // If not in request, check user preference (if logged in)
        else if (Auth::check() && Auth::user()->locale) {
            $locale = Auth::user()->locale;
            App::setLocale($locale);
        }
        // If not user preference, check session
        else if (session()->has('locale')) {
            $locale = session('locale');
            $availableLocales = array_keys(Config::get('td-cost-calcultaror.languages.available', ['en' => 'English']));
            
            if (in_array($locale, $availableLocales)) {
                App::setLocale($locale);
            }
        }

        return $next($request);
    }
}
