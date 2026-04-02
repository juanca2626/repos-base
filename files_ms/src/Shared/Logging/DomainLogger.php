<?php

namespace Src\Shared\Logging;

use Illuminate\Support\Facades\Log;
use Throwable;

class DomainLogger
{
    public function error(
    string $message,
    string $errorCode,
    string $domain,
    string $action,
    string $layer,
    ?string $entityId = null,
    ?Throwable $exception = null,
    array $extra = []
    ): void {

        Log::error($message, [
            'error_code' => $errorCode,
            'domain' => $domain,
            'action' => $action,
            'layer' => $layer,
            'entity_id' => $entityId,
            'exception_class' => $exception ? get_class($exception) : null,
            'exception_message' => $exception?->getMessage(),
            'file' => $exception?->getFile(),
            'line' => $exception?->getLine(),
            ...$extra
        ]);
    }

    public function info(
        string $message,
        string $domain,
        string $action,
        string $layer,
        array $extra = []
    ): void {

        Log::info($message, [
            'domain' => $domain,
            'action' => $action,
            'layer' => $layer,
            ...$extra
        ]);
    }

    public function warning(
        string $message,
        string $domain,
        string $action,
        string $layer,
        array $extra = []
    ): void {

        Log::warning($message, [
            'domain' => $domain,
            'action' => $action,
            'layer' => $layer,
            ...$extra
        ]);
    }

    public function debug(
        string $message,
        string $domain,
        string $action,
        string $layer,
        array $extra = []
    ): void {

        Log::debug($message, [
            'domain' => $domain,
            'action' => $action,
            'layer' => $layer,
            ...$extra
        ]);
    }    

}