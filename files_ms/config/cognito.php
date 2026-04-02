<?php

return [
    'domain' => env('COGNITO_DOMAIN'),
    'clients' => [
        'app1' => [
            'client_id' => env('COGNITO_CLIENT_APP1_ID'),
            'client_secret' => env('COGNITO_CLIENT_APP1_SECRET'),
        ],
        'app2' => [
            'client_id' => env('COGNITO_CLIENT_APP2_ID'),
            'client_secret' => env('COGNITO_CLIENT_APP2_SECRET'),
        ],
    ],
    'ms' => [
        'files_one_db' => env('FILE_ONEDB_URL')
    ],
    'region' => env('AWS_REGION'),
    'user_pool_id' => env('AWS_POOL_ID'),
    'jwks_url' => 'https://cognito-idp.' . env('AWS_REGION') . '.amazonaws.com/' .  env('AWS_POOL_ID') . '/.well-known/jwks.json'
];

