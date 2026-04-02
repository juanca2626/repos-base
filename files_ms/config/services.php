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
        'domain' => env('MAILGUN_DOMAIN'),
        'secret' => env('MAILGUN_SECRET'),
        'endpoint' => env('MAILGUN_ENDPOINT', 'api.mailgun.net'),
        'scheme' => 'https',
    ],

    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'aurora' => [
        'url' => env('AURORA_URL'),
        'client_id' => env('AURORA_CLIENT_ID'),
        'client_secret' => env('AURORA_CLIENT_SECRET'),     
        'endpoint' => env('AURORA_URL'),   
        'user' => env('AURORA_USER'),
        'password' => env('AURORA_PWD'),
    ],

    'quotes_ms' => [
        'endpoint' => env('QUOTE_MS_URL'),
    ],

    'hyperguest' => [
        'endpoint' => env('HYPERGUEST_URL'),
        'token' => env('HYPERGUEST_TOKEN')
    ],

    'stella' => [
        'endpoint' => env('STELLA_URL', '')
    ],

    'a3front' => [
        'endpoint' => env('A3FRONT_URL', '')
    ],

    'ope' => [
        'endpoint' => env('OPE_URL', '')
    ],

    'cognito' => [
        'endpoint' => env('COGNITO_URL', '')
    ],

    'aws_notification_logs' => [
        'endpoint' => env('AWS_NOTIFICATION_LOGS', '')
    ],

    'aws_sns_sqs' => [
        'endpoint' => env('AWS_SNS_SQS', '')
    ],

    'amazon' => [
        'domain' => env('AMAZON',''),
        'env' => env('SQS_QUEUE_NAME',''),
        'env_master_service' => env('SQS_QUEUE_MASTER_SERVICE',''),
        'env_status' => env('SQS_QUEUE_STATUS',''),
        'env_statement' => env('SQS_QUEUE_STATEMENT',''),
        'env_file_update' => env('SQS_QUEUE_FILE_UPDATE',''),
        'env_update_accommodation' => env('SQS_QUEUE_UPDATE_ACCOMMODATION','sqs-files-sync-accommodations-dev')
    ],

    'aviation_stack' => [
        'endpoint' => env('AVIATION_STACK', 'https://api.aviationstack.com'),
        'access_key' => env('AVIATION_ACCESS_KEY', '')
    ],

    'api_gateway' => [
        'endpoint'  => env('APIGW_URL',''),
    ],
];
