<?php

namespace Src\Shared\Logging;

class TraceContext
{
    private static ?string $traceId = null;

    public static function get(): string
    {
        if (!static::$traceId) {
            static::$traceId = self::generate();
        }

        return static::$traceId;
    }

    public static function set(string $traceId): void
    {
        self::$traceId = $traceId;
    }
   
    public static function generate(): string
    {
        static::$traceId = bin2hex(random_bytes(8));
        return static::$traceId;
    }

    public static function reset(): void
    {
        static::$traceId = null;
    }

}