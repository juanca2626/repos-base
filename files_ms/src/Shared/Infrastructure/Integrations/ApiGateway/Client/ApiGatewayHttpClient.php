<?php

namespace Src\Shared\Infrastructure\Integrations\ApiGateway\Client;

use Illuminate\Support\Facades\Http;

class ApiGatewayHttpClient
{
    public function get(string $url, array $params = [])
    {
        return Http::get(
            config('services.api_gateway.endpoint') . $url,
            $params
        );
    }

    public function post(string $url, array $data = [])
    { 
        return Http::post(
            config('services.api_gateway.endpoint') . $url,
            $data
        );
    }
    
 
}
