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
];

