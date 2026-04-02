<?php

namespace Src\Modules\File\Infrastructure\Jobs;
 
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Src\Modules\File\Infrastructure\Persistence\EloquentModel\FileEloquentModel;
use Src\Modules\File\Infrastructure\Persistence\EloquentModel\FileItineraryEloquentModel;
use Src\Modules\File\Infrastructure\ExternalServices\ApiGateway\ApiGatewayExternalService;
use Src\Modules\File\Presentation\Http\Traits\SuggestedAccommodation;

class ProcessUpdateCountryFlightJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;    
    use SuggestedAccommodation;

    private string|null $fileId;

    /**
     * Create a new job instance.
     */
    public function __construct(string|null $fileId = NULL)
    {  
        $this->fileId = $fileId;        
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {            
        $fileItineraryEloquentModel = FileItineraryEloquentModel::where('entity', 'flight')->where(function ($query){
            $query->whereNull('country_in_iso')->orWhereNull('country_out_iso');               
        });

        if($this->fileId)
        {
            $fileItineraryEloquentModel->where('file_id', $this->fileId);
        }

        $flight_countries = $fileItineraryEloquentModel->get();
 
        $valoresUnicos = $flight_countries
            ->flatMap(function ($item) {
                return [$item->city_in_iso, $item->city_out_iso];
            })
            ->filter()
            ->unique()
            ->values()
            ->toArray();
 
        $api_gate_way   = new ApiGatewayExternalService();
        $response       = $api_gate_way->getCitiesIso($valoresUnicos);        
        $ubigeos        = collect($response['data']);
         
        if($ubigeos->count()>0)
        {
            $fileItineraryEloquentModel->chunk( 5000, function($itineraries) use ($ubigeos){
                
                foreach($itineraries as $itinerary)
                {
                    $ubigeo_in = $ubigeos->firstWhere('codciu', $itinerary->city_in_iso);
                    $ubigeo_out = $ubigeos->firstWhere('codciu', $itinerary->city_out_iso);
                    $process = false;
                    if(isset($ubigeo_in) and trim($itinerary->country_in_iso) == '')
                    {
                        $itinerary->country_in_iso = $ubigeo_in['codpais'];
                        $itinerary->country_in_name = $ubigeo_in['pais'];
                        $itinerary->city_in_name = $ubigeo_in['ciudad'];
                        $process = true;
                    }
                    
                    if(isset($ubigeo_out) and trim($itinerary->country_out_iso) == '')
                    {
                        $itinerary->country_out_iso = $ubigeo_out['codpais'];
                        $itinerary->country_out_name = $ubigeo_out['pais'];
                        $itinerary->city_out_name = $ubigeo_out['ciudad'];
                        $process = true;
                    }

                    if($process)
                    {
                        $itinerary->save();
                    }
                }

            }); 
        }
    }
    
}
