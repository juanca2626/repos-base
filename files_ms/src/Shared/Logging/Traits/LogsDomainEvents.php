<?php

namespace Src\Shared\Logging\Traits;
use Src\Shared\Logging\TraceContext;
use Src\Shared\Logging\DomainLogger;
use Illuminate\Support\Facades\Auth;
use Illuminate\Contracts\Queue\ShouldQueue;

use Throwable;

trait LogsDomainEvents
{
 
    protected function logger(): DomainLogger
    {
        return new DomainLogger();
    }
    
    protected function logError(
        string $message,
        string $errorCode,
        ?string $entityId = null,
        ?Throwable $exception = null,
        array $extra = []
    ): void {

        $this->logger()->error(
            message: $message,
            errorCode: $errorCode,
            domain: $this->detectModule(),
            action: $this->detectAction(),
            layer: $this->detectLayer(),
            entityId: $entityId,
            exception: $exception,
            extra: $this->baseContext($extra)
        );
    }

    protected function logInfo(
        string $message,
        array $extra = []
    ): void {

        $this->logger()->info(
            message: $message,
            domain: $this->detectModule(),
            action: $this->detectAction(),
            layer: $this->detectLayer(),
            extra: $this->baseContext($extra)
        );
    }

    protected function logWarning(
        string $message,
        array $extra = []
    ): void {

        $this->logger()->warning(
            message: $message,
            domain: $this->detectModule(),
            action: $this->detectAction(),
            layer: $this->detectLayer(),
            extra: $this->baseContext($extra)
        );
    }

    protected function logDebug(
        string $message,
        array $extra = []
    ): void {

        $this->logger()->debug(
            message: $message,
            domain: $this->detectModule(),
            action: $this->detectAction(),
            layer: $this->detectLayer(),
            extra: $this->baseContext($extra)
        );
    }

    private function baseContext(array $extra = []): array
    {        
        return array_merge(
            $extra,
            $this->jobContext(),
            [
                'user_id' => 1, //Auth::id(),
                'request_id' => request()?->header('X-Request-Id'),            
                'method' => request()?->method(),
                'url' => request()?->fullUrl(),
                'trace_id' => TraceContext::get()
            ]
        );

    }

    private function jobContext(): array
    {
        if ($this instanceof ShouldQueue) {
            return [
                'job' => class_basename($this),
                'queue' => $this->queue ?? 'default',
            ];
        }

        return [];
    }

    private function detectModule(): string
    {
        if (preg_match('/Modules\\\\([^\\\\]+)/', static::class, $matches)) {
            return $matches[1];
        }

        return 'system';
    }

    private function detectLayer(): string
    {
        $class = static::class;

        return match (true) {
            str_contains($class, 'Application') => 'application',
            str_contains($class, 'Domain') => 'domain',
            str_contains($class, 'Infrastructure') => 'infrastructure',
            str_contains($class, 'Http') => 'controller',
            str_contains($class, 'Jobs') => 'job',
            default => 'unknown',
        };
    }

    private function detectAction(): string
    {
        return debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 3)[2]['function'] ?? 'unknown';
    }
}