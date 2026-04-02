<?php

namespace App\Services;

use Aws\SecretsManager\SecretsManagerClient;
use Aws\Exception\AwsException;

class AwsSecretsManager
{
    protected $client;

    public function __construct()
    {
        $this->client = new SecretsManagerClient([
            'version' => '2017-10-17',
            'region'  => config('services.amazon.region'),
        ]);
    }

    public function getSecret(string $secretName)
    {
        $env = config('app.env') || 'local';
        if(in_array($env, ['development', 'production']))
        {
            try {
                $result = $this->client->getSecretValue([
                    'SecretId' => $secretName,
                ]);

                if (isset($result['SecretString'])) {
                    return json_decode($result['SecretString'], true);
                }

                return null;

            } catch (AwsException $e) {
                app('sentry')->captureException($e);
                return null;
            }
        }
    }
}
