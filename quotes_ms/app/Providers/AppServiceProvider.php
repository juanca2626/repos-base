<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Services\Auth\CognitoAuthService;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton(CognitoAuthService::class, function () {
            return new CognitoAuthService();
        });
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }
}
