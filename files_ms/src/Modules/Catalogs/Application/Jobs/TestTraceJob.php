<?php

namespace Src\Modules\Catalogs\Application\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

use Src\Shared\Logging\Traits\LogsDomainEvents;

class TestTraceJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    use LogsDomainEvents;

    public function handle(): void
    {
        $this->logInfo('TestTraceJob started newwwss');

        // Simular un error
        // throw new \Exception('TEST_TRACE_ERROR');

        // $this->logInfo('This line should never execute');
    }
}