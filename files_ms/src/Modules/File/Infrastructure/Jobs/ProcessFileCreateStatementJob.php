<?php

namespace Src\Modules\File\Infrastructure\Jobs;

use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Src\Modules\File\Infrastructure\Persistence\EloquentModel\FileEloquentModel;
use Src\Modules\File\Infrastructure\Persistence\EloquentModel\FileStatementDetailEloquentModel;
use Src\Modules\File\Infrastructure\Persistence\EloquentModel\FileStatementEloquentModel;
use Src\Modules\File\Presentation\Http\Traits\ProcesStatementDetails; 

class ProcessFileCreateStatementJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    use ProcesStatementDetails;
 
    private string $fileId;
    private int $clientHaveCredit;
    private int $clientCreditLine;
    private string $formatDate = 'd/m/Y'; 

    /**
     * Create a new job instance.
     */
    public function __construct(string $fileId, int $clientHaveCredit, float $clientCreditLine)
    { 
        $this->fileId = $fileId; 
        $this->clientHaveCredit = $clientHaveCredit; 
        $this->clientCreditLine = $clientCreditLine; 
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {          
         
        $fileEloquent = FileEloquentModel::with(['statement'], ['itineraries'])->findOrFail($this->fileId); 
        
        if(!$fileEloquent->statement){

            // $time = strtotime($fileEloquent->itineraries->min('date_in'));    
            $time = strtotime($fileEloquent->date_in);

            $deadline = NULL;
            if(!$this->clientHaveCredit)
            {
                $deadline = date('Y-m-d', strtotime('-10 days', $time));
            }

            $total = $fileEloquent->total_amount; 
            $fileStatementModel = FileStatementEloquentModel::create([
                'file_id' => $fileEloquent->id, 
                'user_id' => $fileEloquent->executive_id, 
                'date' => date('Y-m-d'),
                'deadline' => $deadline, 
                'total' => $total
            ]);
            
            $details = $this->create_statement_detail($fileEloquent);
        
            if(count($details)>0){
                foreach($details as $detail)
                {              
                    FileStatementDetailEloquentModel::create([
                        'file_statement_id' => $fileStatementModel->id, 
                        'description' => $detail['description'], 
                        'quantity' => $detail['quantity'],
                        'unit_price' => $detail['unit_price'],
                        'amount' => $detail['amount'],
                        'type_room' => $detail['type_room'],
                        'type_pax' => $detail['type_pax']
                    ]);
                }
            }

        }

    }

 
    
}
