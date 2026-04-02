<?php

namespace Src\Shared\Infrastructure\Integrations\Aurora\Auth;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Src\Shared\Logging\Traits\LogsDomainEvents;

class AuroraAuthService
{
    use LogsDomainEvents; 
    private string $cacheKey = 'aurora_service_token';

    public function getToken(): string
    {        
        return Cache::remember($this->cacheKey, now()->addHours(12), function () {
            $this->logInfo('sin cache activo');
            $response = Http::post(
                config('services.aurora.url') . '/api/auth/service-token',
                [
                    'client_id' => config('services.aurora.client_id'),
                    'client_secret' => config('services.aurora.client_secret'),
                ]
            );

            if (!$response->successful()) {
                throw new \RuntimeException('Aurora authentication failed');
            }

            return $response->json()['access_token'];
        });
    }

    public function clearToken(): void
    {
        Cache::forget($this->cacheKey);
    }
}