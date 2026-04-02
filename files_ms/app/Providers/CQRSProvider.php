<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;


class CQRSProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
//        $this->app->singleton(CreateFileCommand::class);
//        $this->app->singleton(UpdateProductCommandHandler::class);
//        $this->app->singleton(GetUserQueryHandler::class);
//        $this->app->singleton(GetProductQueryHandler::class);
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        // Puedes realizar configuraciones adicionales aquí si es necesario
    }
}
