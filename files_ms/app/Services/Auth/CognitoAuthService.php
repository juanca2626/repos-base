<?php

namespace App\Services\Auth;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use App\Services\Auth\Exceptions\AuthException;
use Illuminate\Support\Facades\Log;

class CognitoAuthService
{
    private string $cognitoDomain;
    private string $clientId;
    private string $clientSecret;

    public function __construct()
    {
        $config = config('cognito.clients.app2');
        $this->cognitoDomain = config('cognito.domain');
        $this->clientId = $config['client_id'];
        $this->clientSecret = $config['client_secret'];
    }

    /**
     * Obtiene headers de autorización con token Bearer.
     */
    public function getAuthorizationHeaders(): array
    {
        $token = $this->getToken();
        return ['Authorization' => 'Bearer ' . $token];
    }

    /**
     * Obtiene el token de Cognito (con cache).
     */
    private function getToken(): string
    {        
        return Cache::remember('cognito_token', 3600, function () {
            $response = Http::asForm()->post("{$this->cognitoDomain}/oauth2/token", [
                'grant_type' => 'client_credentials',
                'client_id' => $this->clientId,
                'client_secret' => $this->clientSecret,
            ]);

            if ($response->failed()) {
                throw new AuthException(
                    "Error al obtener token: " . $response->body(),
                    $response->status()
                );
            }

            return $response->json('access_token');
        });
    }

    /**
     * Limpia el token cacheado.
     */
    public function clearTokenCache(): void
    {
        Cache::forget('cognito_token');
    }
}
