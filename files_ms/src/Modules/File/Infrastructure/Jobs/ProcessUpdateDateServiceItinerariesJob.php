<?php

namespace Src\Modules\File\Infrastructure\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Src\Modules\File\Infrastructure\Persistence\EloquentModel\FileItineraryEloquentModel;
use Carbon\Carbon;

use Src\Modules\File\Infrastructure\ExternalServices\ApiGateway\ApiGatewayExternal;

class ProcessUpdateDateServiceItinerariesJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private $itineraries;

    /**
     * Create a new job instance.
     */

    public function __construct($itineraries) 
    {  
        $this->itineraries = $itineraries;  
        
        $this->itineraries = $itineraries;

        $equivalences = [];
        foreach($this->itineraries as $itinerary)
        {

            $totalAdults = $itinerary->total_adults;
            $totalChildren = $itinerary->total_children;
            $totalInfants = $itinerary->total_infants;
            $totalPassengers = $totalAdults + $totalChildren + $totalInfants;
            $equivalences[] = [
                "code" => $itinerary->object_code,
                "date_in" => Carbon::parse($itinerary->date_in)->format('d/m/Y'),
                "total_passengers" => $totalPassengers,
                "total_children" => $totalChildren,
                "start_time" => $itinerary->start_time,
            ];

        }

        $equivalencesData = [
            "equivalences" => $equivalences
        ];

        $this->onQueue("update_master_service");
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $aurora = new ApiGatewayExternal();

        $equivalences = [];
        foreach($this->itineraries as $itinerary)
        {

            $totalAdults = $itinerary->total_adults;
            $totalChildren = $itinerary->total_children;
            $totalInfants = $itinerary->total_infants;
            $totalPassengers = $totalAdults + $totalChildren + $totalInfants;
            $equivalences[] = [
                "code" => $itinerary->object_code,
                "date_in" => Carbon::parse($itinerary->date_in)->format('d/m/Y'),
                "total_passengers" => $totalPassengers,
                "total_children" => $totalChildren,
                "start_time" => $itinerary->start_time,
            ];

        }

        $equivalencesData = [
            "equivalences" => $equivalences
        ];

        $masterServices = $aurora->getMasterServices($equivalencesData);
        $masterServices = collect($masterServices);

        foreach($this->itineraries as $itinerary)
        {
            $equivalence = $masterServices->filter(function($item) use ($itinerary){

                $totalAdults = $itinerary->total_adults;
                $totalChildren = $itinerary->total_children;
                $totalInfants = $itinerary->total_infants;
                $totalPassengers = $totalAdults + $totalChildren + $totalInfants;

                return ($item->code == $itinerary->object_code and
                        $item->date_in == Carbon::parse($itinerary->date_in)->format('d/m/Y') and
                        $item->total_passengers == $totalPassengers and
                        $item->total_children == $totalChildren and
                        $item->start_time == $itinerary->start_time
                );

            })->first();

            if($equivalence)
            {
                foreach($itinerary->services as $service)
                {
                    $master_service = collect($equivalence->master_services)->filter(function($item_equivalence) use ($service){
                        return ($item_equivalence->master_service_id == $service->master_service_id);
                    })->first();

                    if($master_service)
                    {
                        foreach($service->compositions as $composition)
                        {
                            $service_composition = collect($master_service->components)->filter(function($item_service) use ($composition){
                                return ($item_service->composition_id == $composition->composition_id);
                            })->first();

                            if($service_composition)
                            {
                                $composition->date_in = Carbon::createFromFormat('d/m/Y', $service_composition->date_in)->format('Y-m-d');
                                $composition->date_out = Carbon::createFromFormat('d/m/Y', $service_composition->date_out)->format('Y-m-d');
                                $composition->save();
                            }
                        }

                        $service->date_in = Carbon::createFromFormat('d/m/Y', $master_service->date_in)->format('Y-m-d');
                        $service->date_out = Carbon::createFromFormat('d/m/Y', $master_service->date_out)->format('Y-m-d');
                        $service->save();
                    }

                }

                $itinerary->update_master_service = 1;
                $itinerary->save();
            }
        }



    }

}
