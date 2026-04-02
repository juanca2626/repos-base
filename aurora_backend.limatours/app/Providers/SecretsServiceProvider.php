<?php

namespace App\Providers;

use App\Services\AwsSecretsManager;
use Illuminate\Support\ServiceProvider;
use Sentry\Laravel\Facade as Sentry;

class SecretsServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(AwsSecretsManager $secrets): void
    {
        /*
        try
        {
            $secret = $secrets->getSecret('BrevoApiKey');

            if($secret)
            {
                config([
                    'services.sendinblue.masi_api_key_v3' => $secret['key'] ?? config('services.sendinblue.masi_api_key_v3'),
                ]);
            }
        }
        catch(\Exception $ex)
        {
            // Reportando error en Sentry..
            Sentry::captureException($ex);
        }
        */
    }
}
