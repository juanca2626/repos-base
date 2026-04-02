<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\App;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SetLocale
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        //Todo: definimos el idioma para la aplicacion
        $lang = $request->input('lang');
        if (!in_array($lang, ['es', 'en', 'pt'])) { 
            App::setLocale('es');
        } else { 
            App::setLocale($lang);
        }
        return $next($request);
    }
}
