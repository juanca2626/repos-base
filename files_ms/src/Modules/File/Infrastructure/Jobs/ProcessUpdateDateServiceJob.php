<?php

namespace Src\Modules\File\Infrastructure\Jobs;
 
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels; 
use Src\Modules\File\Infrastructure\Persistence\EloquentModel\FileItineraryEloquentModel;   

class ProcessUpdateDateServiceJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels; 

    private $itineraries;

    /**
     * Create a new job instance.
     */
    public function __construct()
    {        
        $this->onQueue("update_master_service");
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {            
         $fileItineraryEloquentModel = FileItineraryEloquentModel::with('services.compositions')->where('entity', 'service')->where('service_category_id', 2)
                                        ->where('update_master_service', 0)->limit(12);
         
        if(count($fileItineraryEloquentModel->get())>0)
        {      
            $fileItineraryEloquentModel->chunk( 4, function($itineraries) {                    
                dispatch(new ProcessUpdateDateServiceItinerariesJob($itineraries));     
            }); 

        }


    }
    
}
