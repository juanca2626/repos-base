<?php

namespace Src\Shared\Infrastructure\Auth;

use Src\Shared\Domain\Auth\AuthUser;

class UserContext
{
    public static function user(): ?AuthUser
    {
        return app()->bound(AuthUser::class)
            ? app(AuthUser::class)
            : null;
    }

    public static function id(): ?int
    {
        return self::user()?->id;
    }

    public static function code(): ?string
    {
        return self::user()?->code;
    }

    public static function email(): ?string
    {
        return self::user()?->email;
    }

    public static function permissions(): array
    {
        return self::user()?->permissions ?? [];
    }
}

 