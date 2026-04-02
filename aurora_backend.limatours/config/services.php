<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Stripe, Mailgun, SparkPost and others. This file provides a sane
    | default location for this type of information, allowing packages
    | to have a conventional place to find your various credentials.
    |
    */

    'mailgun' => [
        'domain' => env('MAILGUN_DOMAIN'),
        'secret' => env('MAILGUN_SECRET'),
        'endpoint' => env('MAILGUN_ENDPOINT', 'api.mailgun.net'),
    ],

    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'sparkpost' => [
        'secret' => env('SPARKPOST_SECRET'),
    ],

    'stripe' => [
        'model' => App\User::class,
        'key' => env('STRIPE_KEY'),
        'secret' => env('STRIPE_SECRET'),
        'webhook' => [
            'secret' => env('STRIPE_WEBHOOK_SECRET'),
            'tolerance' => env('STRIPE_WEBHOOK_TOLERANCE', 300),
        ],
    ],

    'stella' => [
        'domain' => env('API_STELLA_URL'),
    ],

    'files_onedb' => [
        'domain' => env('API_FILES_ONEDB_MS'),
    ],

    'accountancy_onedb' => [
        'domain' => env('API_ACCOUNTANCY_ONEDB_MS'),
    ],

    'sendinblue' => [
        'api_key_v3' => env('SENDINBLUE_KEY_V3'),
        'api_key_v2' => env('SENDINBLUE_KEY_V2'),
        'masi_api_key_v3' => env('MASI_BREVO_API_KEY'),
    ],

    'aurora_front' => [
        'domain' => env('AURORA_FRONT_URL', 'https://aurora.limatours.com.pe/'),
    ],

    'aurora_extranet' => [
        'domain' => env('AURORA_EXTRANET', 'https://extranet.litoapps.com'),
    ],

    'amazon' => [
        'region' =>env('AWS_DEFAULT_REGION', 'us-east-1'),
        'domain' => env('AMAZON','https://t1hg8s359g.execute-api.us-east-1.amazonaws.com/dev'),
        'env' => env('SQS_QUEUE_NAME','')
    ],

    'aurora_files' => [
        'domain' => env('FILES_MS', 'https://filesms.limatours.dev'),
        'env' => env('FILES_QUEUE_NAME',''),
    ],

    'cognito' => [
        'endpoint' => env('COGNITO_URL', 'https://oo3jqwjgy3.execute-api.us-east-1.amazonaws.com/'),
        'user' => env('AURORA_USER',''),
        'password' => env('AURORA_PWD','')
    ],

    'hyperguest' => [
        'domain' => env('API_HYPERGUEST_URL'),
    ],

    'cloudinary' => [
        'domain' => 'https://res.cloudinary.com/litodti/image/upload'
    ],

    'twilio' => [
        'account_sid' => env('TWILIO_ACCOUNT_SID'),
        'auth_token' => env('TWILIO_AUTH_TOKEN'),
        'number' => env('TWILIO_NUMBER'),
    ],

    'weather' => [
        'api_key' => env('WEATHER_API_KEY'),
    ],

    'masi' => [
        'domain' => env('MIX_MASI_EXTERNAL_URL')
    ],

    'quotes' => [
        'domain' => env('MIX_QUOTES_EXTERNAL_URL')
    ],

    'files_service' => [
        'client_id' => env('FILES_CLIENT_ID'),
        'client_secret' => env('FILES_CLIENT_SECRET'),
        'email' => env('FILES_SERVICE_EMAIL'),
    ],

    'n8n' => [
        'webhook' => env('N8N_WEBHOOK_URL')
    ]
];
