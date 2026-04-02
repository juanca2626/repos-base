<?php
namespace Src\Shared\Infrastructure\Integrations\Cognito\Auth;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;

class CognitoAuthService
{
    private string $cacheKey = 'cognito_service_token';
    
    public function getToken(): string
    {
        // return Cache::remember($this->cacheKey, now()->addHours(12), function () {
            $clientId = config('cognito.clients.app2.client_id');
            $clientSecret = config('cognito.clients.app2.client_secret');

            $response = Http::asForm()
                ->withBasicAuth($clientId, $clientSecret)
                ->post(
                    config('cognito.domain') . '/oauth2/token',
                    [
                        'grant_type' => 'client_credentials',
                    ]
                );

            if (!$response->successful()) {            
                throw new \RuntimeException('Cognito authentication failed');
            }
    
            return $response->json()['access_token'];
        // });
    }
        

    public function clearToken(): void
    {
        Cache::forget($this->cacheKey);
    }
}
