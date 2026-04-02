<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Foundation\Http\Middleware\CheckForMaintenanceMode as Middleware;

class CheckForMaintenanceMode extends Middleware
{
    protected $app;

    public function __construct(Application $app)
    {
        $this->app = $app;
    }
    /**
     * The URIs that should be reachable while maintenance mode is enabled.
     *
     * @var array
     */
    protected $except = [
        //
    ];

    public function handle($request, Closure $next)
    {
        if (! $this->app->isDownForMaintenance()) {
            return $next($request);
        }

        return response()->view('errors.503');
    }
}
