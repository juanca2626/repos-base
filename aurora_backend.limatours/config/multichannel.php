<?php

return [
    'hyperguest' => [
        'base_url' => env('MULTICHANNEL_BASE_URL', 'https://api.multichannel.example.com'),
        'api_key' => env('MULTICHANNEL_API_KEY', 'your_api_key_here'),
        'static_api_url' => env('HYPERGUEST_STATIC_API_URL', 'https://hg-static.hyperguest.com'),
        'api_key_hyperguest' => env('API_KEY_HYPERGUEST', '')
    ]
];
