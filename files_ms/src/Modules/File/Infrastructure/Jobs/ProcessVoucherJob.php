<?php

namespace Src\Modules\File\Infrastructure\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Src\Modules\File\Application\UseCases\Queries\ProcessVouchers;

use Throwable;

class ProcessVoucherJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3; // Reintentar hasta 3 veces.

    public int $retryAfter = 60; // Reintentar después de 60 segundos.
  
    private array $data;

    public function __construct(array $data = [])
    {
        $this->data = $data;
    }

    public function handle(ProcessVouchers $data)
    {
        $data->execute($this->data);
    }

     public function failed(Throwable $exception): void
    {
        // registra el fallo o enviar notificaciones.
        \Log::error('Job failed: ' . $exception->getMessage());
    }
}