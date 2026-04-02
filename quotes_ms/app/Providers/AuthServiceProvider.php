<?php

namespace App\Providers;

use App\Guard\JWTGuard;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
    ];

    /**
     * Register any authentication / authorization services.
     *
     * @return void
     */
    public function boot()
    {
        $this->registerPolicies();

        $this->app['auth']->extend(
            'jwt-auth',
            function ($app, $name, array $config) {
                $guard = new JWTGuard(
                    $app['tymon.jwt'],
                    $app['request']
                );
                $app->refresh('request', $guard, 'setRequest');

                return $guard;
            }
        );
    }
}
