<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, Postmark, AWS and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'mailgun' => [
        'domain'   => env('MAILGUN_DOMAIN'),
        'secret'   => env('MAILGUN_SECRET'),
        'endpoint' => env('MAILGUN_ENDPOINT', 'api.mailgun.net'),
        'scheme'   => 'https',
    ],

    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
    ],

    'ses' => [
        'key'    => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'sparkpost' => [
        'secret' => env('SPARKPOST_SECRET'),
    ],

    'stripe' => [
        'model'   => App\User::class,
        'key'     => env('STRIPE_KEY'),
        'secret'  => env('STRIPE_SECRET'),
        'webhook' => [
            'secret'    => env('STRIPE_WEBHOOK_SECRET'),
            'tolerance' => env('STRIPE_WEBHOOK_TOLERANCE', 300),
        ],
    ],

    'stella' => [
        'domain' => env('API_STELLA_URL'),
    ],

    'aurora' => [
        'domain' => env('API_AURORA_URL'),
        'user' => env('AURORA_USER'),
        'password' => env('AURORA_PWD'),
    ],

    'sendinblue' => [
        'api_key_v3'      => env('SENDINBLUE_KEY_V3'),
        'api_key_v2'      => env('SENDINBLUE_KEY_V2'),
        'masi_api_key_v3' => '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',
    ],

    'aurora_front' => [
        'domain' => env('AURORA_FRONT_URL', 'https://aurora.limatours.com.pe/'),
    ],

    'aurora_extranet' => [
        'domain' => env('AURORA_EXTRANET', 'http://extranet.litoapps.com'),
    ],

    'api_gateway' => [
        'endpoint' => env('API_GATEWAY_ENDPOINT', 'http://aurora_backend.limatours.test/'),
    ],

];
