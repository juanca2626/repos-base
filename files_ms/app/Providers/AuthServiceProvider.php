<?php

namespace App\Providers;

// use Illuminate\Support\Facades\Gate;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Src\Shared\Domain\Auth\TokenValidatorInterface;
// use Src\Shared\Infrastructure\Auth\CognitoTokenValidator;

class AuthServiceProvider_DELETE extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        //
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        //
    }

    public function register()
    {
        // $this->app->bind(TokenValidatorInterface::class, CognitoTokenValidator::class);
    }
}
