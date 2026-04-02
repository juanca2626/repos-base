<?php
namespace Src\Shared\Infrastructure\Integrations\Cognito\Client;

use Illuminate\Support\Facades\Http;
use Src\Shared\Infrastructure\Integrations\Cognito\Auth\CognitoAuthService;

class CognitoHttpClient
{
    public function __construct(
        private CognitoAuthService $authService
    ) {}

    public function get(string $url, array $params = [])
    {
        $response = $this->makeRequest('get', $url, $params);

        if ($response->status() === 401) {

            $this->authService->clearToken();

            $response = $this->makeRequest('get', $url, $params);
        }

        return $response;
    }

    public function post(string $url, array $data = [])
    {
        $response = $this->makeRequest('post', $url, $data);

        if ($response->status() === 401) {

            $this->authService->clearToken();

            $response = $this->makeRequest('post', $url, $data);
        }

        return $response;
    }

    private function makeRequest(string $method, string $endpoint, array $data = [])
    {
        $token = $this->authService->getToken();

        return Http::withToken($token)
            ->$method(config('cognito.ms.files_one_db') . $endpoint, $data);
    }
}
