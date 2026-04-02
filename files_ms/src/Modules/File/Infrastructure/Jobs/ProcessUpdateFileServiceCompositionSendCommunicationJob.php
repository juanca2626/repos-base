<?php

namespace Src\Modules\File\Infrastructure\Jobs;

use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Src\Modules\File\Infrastructure\Persistence\EloquentModel\FileCompositionSupplierEloquentModel; 

class ProcessUpdateFileServiceCompositionSendCommunicationJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;    
 
    

    /**
     * Create a new job instance.
     */
    public function __construct( )
    {    
                                    
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {    

        FileCompositionSupplierEloquentModel::where('send_communication', 0)->chunk(5000, function ($suppliers) {
            foreach ($suppliers as $supplier) {
                if($supplier->reservation_for_send == 1){
                $supplier->send_communication = 'S';
                }else{
                $supplier->send_communication = 'N';
                }
                $supplier->save();
            }
        });              
    }
 
    
}
