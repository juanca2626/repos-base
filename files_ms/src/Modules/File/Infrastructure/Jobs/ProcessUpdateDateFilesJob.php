<?php

namespace Src\Modules\File\Infrastructure\Jobs;
 
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Src\Modules\File\Infrastructure\Persistence\EloquentModel\FileEloquentModel;
use Src\Modules\File\Presentation\Http\Traits\SuggestedAccommodation;

class ProcessUpdateDateFilesJob implements ShouldQueue
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
        $query="           
                select files.id, files.file_number ,files.date_in, files.date_out, 
                min(file_itineraries.date_in) as date_in_itinerary, 
                max(file_itineraries.date_out) as date_aut_itinerary 
                from files left join file_itineraries on files.id=file_itineraries.file_id
                GROUP BY files.id, files.date_in, files.date_out
                HAVING 
		        files.date_in <> MIN(file_itineraries.date_in) OR 
                files.date_out <> MAX(file_itineraries.date_out) OR 
                files.date_in is null OR 
                files.date_out is null";

        $results = DB::select($query);
 
        foreach ($results as $result) {  

            $fileEloquement = FileEloquentModel::find($result->id);

            if($fileEloquement->date_in != $result->date_in_itinerary)                                   
            {
                $fileEloquement->date_in = $result->date_in_itinerary;
            }

            if($fileEloquement->date_out != $result->date_aut_itinerary)                                   
            {
                $fileEloquement->date_out = $result->date_aut_itinerary;
            }

            $fileEloquement->save();
        }
             
    }
    
}
