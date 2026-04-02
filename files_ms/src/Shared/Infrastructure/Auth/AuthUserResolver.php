<?php

namespace Src\Shared\Infrastructure\Auth;

use Illuminate\Support\Facades\Http;
use RuntimeException;

class AuthUserResolver
{
    public function resolve(string $token): array
    {
        $response = Http::withToken($token)
            ->get(config('services.cognito.endpoint') . '/api/v1/auth/me');

        if (!$response->successful()) {          
            throw new RuntimeException('Unable to resolve authenticated user');
        }

        return $response->json();
    }
}