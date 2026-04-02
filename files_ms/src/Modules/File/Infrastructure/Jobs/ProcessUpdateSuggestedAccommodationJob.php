<?php

namespace Src\Modules\File\Infrastructure\Jobs;
 
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Src\Modules\File\Infrastructure\Persistence\EloquentModel\FileEloquentModel; 
use Src\Modules\File\Presentation\Http\Traits\SuggestedAccommodation;

class ProcessUpdateSuggestedAccommodationJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;    
    use SuggestedAccommodation;
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
        FileEloquentModel::with('itineraries.rooms')
        ->where('suggested_accommodation_sgl',0)->where('suggested_accommodation_dbl',0)->where('suggested_accommodation_tpl',0)
        ->whereHas('itineraries', function ($query){
            $query->where('entity', 'hotel'); 
        })
        ->chunk(5000, function ($files) {
            foreach ($files as $file) {                 
                $accommodation = $this->suggested_accommodation($file);     
                $file->suggested_accommodation_sgl = $accommodation['sgl'];                      
                $file->suggested_accommodation_dbl = $accommodation['dbl'];
                $file->suggested_accommodation_tpl = $accommodation['tpl'];
                $file->save();
            }
        });
             
    }
    
}
