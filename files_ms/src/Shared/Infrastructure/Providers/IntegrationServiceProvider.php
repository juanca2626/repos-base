<?php

namespace Src\Shared\Infrastructure\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Queue\Events\JobProcessing;
use Illuminate\Support\Facades\Queue;
use Src\Shared\Logging\TraceContext;
use Illuminate\Support\Facades\Event;
use Illuminate\Console\Events\CommandStarting;
use Illuminate\Queue\Events\JobProcessed;


// Implementations
use Src\Shared\Infrastructure\Integrations\Aurora\Auth\AuroraAuthService;
use Src\Shared\Infrastructure\Integrations\Aurora\Client\AuroraHttpClient; 
use Src\Shared\Infrastructure\Integrations\ApiGateway\Client\ApiGatewayHttpClient;
use Src\Shared\Infrastructure\Integrations\Cognito\Auth\CognitoAuthService;
use Src\Shared\Infrastructure\Integrations\Cognito\Client\CognitoHttpClient;



class IntegrationServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        /*
        |--------------------------------------------------------------------------
        | Aurora Auth Service
        |--------------------------------------------------------------------------
        */
        $this->app->singleton(AuroraAuthService::class, function ($app) {
            return new AuroraAuthService();
        });

        /*
        |--------------------------------------------------------------------------
        | Aurora HTTP Client
        |--------------------------------------------------------------------------
        */
        $this->app->singleton(AuroraHttpClient::class, function ($app) {
            return new AuroraHttpClient(
                $app->make(AuroraAuthService::class)
            );
        });


        /*
        |--------------------------------------------------------------------------
        | Cognito Auth Service
        |--------------------------------------------------------------------------
        */
        $this->app->singleton(CognitoAuthService::class, function ($app) {
            return new CognitoAuthService();
        });

        /*
        |--------------------------------------------------------------------------
        | Cognito HTTP Client
        |--------------------------------------------------------------------------
        */
        $this->app->singleton(CognitoHttpClient::class, function ($app) {
            return new CognitoHttpClient(
                $app->make(CognitoAuthService::class)
            );
        });

         /*
        |--------------------------------------------------------------------------
        | ApiGateway HTTP Client
        |--------------------------------------------------------------------------
        */
        $this->app->singleton(ApiGatewayHttpClient::class, function () {
            return new ApiGatewayHttpClient();
        });
        
    }

    public function boot(): void
    {

        Queue::createPayloadUsing(function () {
            return ['trace_id' => TraceContext::get()];
        });

        Queue::before(function (JobProcessing $event) {

            $payload = $event->job->payload();

            $traceId = $payload['trace_id'] ?? null;

            if ($traceId) {
                TraceContext::set($traceId);
            }
        });

        Queue::after(function (JobProcessed $event) {
            TraceContext::reset();
        });

        Event::listen(CommandStarting::class, function () {
            TraceContext::generate();
        });


    }
}