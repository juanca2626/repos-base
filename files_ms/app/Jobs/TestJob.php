<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class TestJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct()
    {
        //
    }

    public function handle()
    {
        Log::info('TestJob está siendo procesado por el worker: ' . date('Y-m-d H:i:s'));
        sleep(5); // Simula algún trabajo que toma tiempo
        Log::info('TestJob ha sido completado: ' . date('Y-m-d H:i:s'));
    }
}
