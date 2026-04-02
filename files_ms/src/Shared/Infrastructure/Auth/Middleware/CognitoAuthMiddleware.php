<?php

namespace Src\Shared\Infrastructure\Auth\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Src\Shared\Domain\Auth\AuthUser;
use Src\Shared\Infrastructure\Auth\AuthUserResolver;
    
class CognitoAuthMiddleware
{
    public function __construct(
        private AuthUserResolver $resolver
    ) {}

    public function handle(Request $request, Closure $next)
    {
        $token = $request->bearerToken();

        if (!$token) {
            return response()->json([
                'error' => 'Authorization token missing'
            ], 401);
        }

        $userData = Cache::remember(
            'auth_user_' . md5($token),
            300,
            fn() => $this->resolver->resolve($token)
        );

        $authUser = new AuthUser(
            id: $userData['id'],
            code: $userData['code'],
            name: $userData['name'],
            email: $userData['email'],
            user_type_id: $userData['user_type_id'],
            is_kam: $userData['is_kam'] ?? 0,
            language_id: $userData['language_id'],
            rol: $userData['rol'],
            status: $userData['status'],
            permissions: $userData['permissions'] ?? [],
            department: $userData['department'] ?? []
        );

        app()->instance(AuthUser::class, $authUser);

        return $next($request);
    }
}