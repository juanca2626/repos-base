<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\App;

class SetLocale
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        //Todo: definimos el idioma para la aplicacion
        $lang = $request->input('lang');
        if (!in_array($lang, ['es', 'en', 'pt'])) {
            App::setLocale('en');
        } else {
            App::setLocale($lang);
        }
        return $next($request);
    }
}
