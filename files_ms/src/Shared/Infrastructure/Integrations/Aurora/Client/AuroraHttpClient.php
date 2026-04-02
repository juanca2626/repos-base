<?php

namespace Src\Shared\Infrastructure\Integrations\Aurora\Client;

use Illuminate\Support\Facades\Http;
use Src\Shared\Infrastructure\Integrations\Aurora\Auth\AuroraAuthService;

class AuroraHttpClient
{
    public function __construct(
        private AuroraAuthService $authService
    ) {}

    public function get(string $endpoint, array $params = [])
    {
        $response = $this->makeRequest('get', $endpoint, $params);

        if ($response->status() === 401) {
            // Token expirado → limpiar y reintentar
            $this->authService->clearToken();

            $response = $this->makeRequest('get', $endpoint, $params);
        }

        return $response;
    }

    private function makeRequest(string $method, string $endpoint, array $data = [])
    {
        $token = $this->authService->getToken();

        return Http::withToken($token)
            ->$method(config('services.aurora.url') . $endpoint, $data);
    }
}