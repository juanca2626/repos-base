<?php

namespace Src\Modules\File\Infrastructure\Jobs;

use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels; 
use Src\Modules\File\Presentation\Http\Traits\ProcesStatementDetails; 
use Src\Modules\File\Presentation\Http\Traits\SqsNotification;

class ProcessExecuteStatementJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    use ProcesStatementDetails;
    use SqsNotification;

    private string $fileId;
    private string $executive_id;

    /**
     * Create a new job instance.
     */
    public function __construct(string $fileId, string $executive_id)
    { 
        $this->fileId = $fileId;
        $this->executive_id = $executive_id;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {          
        $this->send_create_statement([
            'success' => true,
            'type' => 'statement',
            'file_id' => $this->fileId,
            'userId' => $this->executive_id,
            'action' => 'create'
        ]);

    }    
}
