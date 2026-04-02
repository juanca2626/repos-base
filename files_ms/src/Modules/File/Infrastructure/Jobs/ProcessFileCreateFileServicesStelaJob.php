<?php

namespace Src\Modules\File\Infrastructure\Jobs;

use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Src\Modules\File\Infrastructure\Persistence\EloquentModel\FileItineraryEloquentModel;
use Src\Modules\File\Infrastructure\ExternalServices\Aurora\AuroraExternalApiService;
use Src\Modules\File\Infrastructure\ExternalServices\ApiGateway\ApiGatewayExternal;
use Src\Modules\File\Presentation\Http\Traits\CalculateProfitability;
use Src\Modules\File\Presentation\Http\Traits\ImportFileServiceStela;

use function Pest\Laravel\json;

class ProcessFileCreateFileServicesStelaJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    use CalculateProfitability, ImportFileServiceStela;

    private string $file_id;
    private string $file_number;

    /**
     * Create a new job instance.
     */
    public function __construct(string $file_id, string $file_number)
    {
        $this->file_id = $file_id;
        $this->file_number = $file_number;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $files_onedb = new ApiGatewayExternal();

        $file_services_stela = (array) $files_onedb->search_file_services_stela($this->file_number);

        $services_aurora = $this->getEquivalenceAurora($file_services_stela);

        foreach($file_services_stela as $itinerary_stela)
        {
            $itinerary_params = $this->getItinerary($itinerary_stela, $services_aurora);
            // $fileItineraryEloquentModel = $this->create_itinerary($itinerary_params);
            // $fileItineraryEloquentModel->save();
        }


        // DB::transaction(function () use ($masterServices, $fileItineraries) {
        //     $this->processMasterServices($masterServices, $fileItineraries);
        //     $this->updatePassengerServices($fileItineraries);
        //     $this->updateAmountItinerary();
        //     $this->updateTypeRoomPassenger();
        //     // $this->updateHourServices();
        // });
    }

    public function getItinerary($itinerary_stela, $services_aurora)
    {
        $itinerary_params = [];
        if($itinerary_stela->tipsvs == 'HTL'){
            // $itinerary_params = $this->itinerary_hotel($services_aurora, $itinerary_stela);

        }

        if($itinerary_stela->tipsvs == 'SVS'){
            $itinerary_params = $this->itinerary_service($services_aurora, $itinerary_stela);
            dd($itinerary_params);
        }

        if($itinerary_stela->tipsvs == 'FLY'){
            $itinerary_params = $this->itinerary_flight($services_aurora, $itinerary_stela);
        }

        return $itinerary_params;

    }

    public function itinerary_hotel($services_aurora, $itinerary_stela)
    {

        $category = NULL;
        $aurora_select = null;
        foreach($services_aurora['hotels'] as $aurora)
        {
            if($aurora->hotel_code == trim($itinerary_stela->codsvs))
            {
                $aurora_select = $aurora;
                if(isset($aurora->hoteltypeclass)){
                    $category = $aurora->hoteltypeclass[0]->typeclass->translations[0]->value;
                }
                break;
            }
        }

        $data = [];
        $data['file_id'] = $this->file_id;
        $data['entity'] = 'hotel';
        $data['object_id'] = isset($aurora_select) ?  $aurora_select->hotel_id : NULL;
        $data['name'] = isset($aurora_select) ?  $aurora_select->hotel_name : $itinerary_stela->name;
        $data['category'] = $category;
        $data['object_code'] = $itinerary_stela->codsvs;
        $data['country_in_iso'] = isset($aurora_select) ?  $aurora_select->country->iso : NULL ;
        $data['country_in_name'] = isset($aurora_select) ?  $aurora_select->country->translations[0]->value : NULL;
        $data['city_in_iso'] = isset($aurora_select) ?  $aurora_select->city->iso : NULL;
        $data['city_in_name'] = isset($aurora_select) ?  $aurora_select->city->translations[0]->value : NULL;
        $data['zone_in_iso'] = NULL;
        $data['zone_in_id'] = isset($aurora_select) ?  $aurora_select->zone->id : NULL;
        $data['zone_in_airport'] = NULL;

        $data['country_out_iso'] = isset($aurora_select) ?  $aurora_select->country->iso : NULL;
        $data['country_out_name'] = isset($aurora_select) ?  $aurora_select->country->translations[0]->value : NULL;
        $data['city_out_iso'] = isset($aurora_select) ?  $aurora_select->city->iso : NULL;
        $data['city_out_name'] = isset($aurora_select) ?  $aurora_select->city->translations[0]->value : NULL;
        $data['zone_out_iso'] = NULL;
        $data['zone_out_id'] = isset($aurora_select) ?  $aurora_select->zone->id : NULL;
        $data['zone_out_airport'] = NULL;
        $data['start_time'] = $itinerary_stela->horin;
        $data['departure_time'] = $itinerary_stela->horout;
        $data['date_in'] = $itinerary_stela->fecini;
        $data['date_out'] = $itinerary_stela->fecfin;
        $data['total_adults'] = $itinerary_stela->canadl;
        $data['total_children'] = $itinerary_stela->canchd;
        $data['total_infants'] = $itinerary_stela->caninf;
        $data['markup_created'] = NULL;
        $data['total_amount'] = $itinerary_stela->totcos;
        $data['total_cost_amount'] = $itinerary_stela->totven;
        $data['serial_sharing'] = 0;
        $data['executive_code'] = 'SBR'; // $itinerary_stela->totven'];
        $data['status'] = 1; //$itinerary_stela->totven'];
        $data['confirmation_status'] = 1; //$itinerary_stela->totven'];
        $data['policies_cancellation_service'] = NULL;
        $data['data_passengers'] = NULL;
        $data['service_rate_id'] = NULL;
        $data['is_in_ope'] = 0;
        $data['sent_to_ope'] = 0;
        $data['hotel_origin'] = NULL;
        $data['hotel_destination'] = NULL;
        $data['service_supplier_code'] = NULL;
        $data['service_supplier_name'] = NULL;
        $data['protected_rate'] = 0;
        $data['view_protected_rate'] = false;
        $data['service_category_id'] = NULL;
        $data['service_sub_category_id'] = NULL;
        $data['service_type_id'] = NULL;
        $data['service_summary'] = NULL;
        $data['service_itinerary'] = NULL;
        $data['add_to_statement'] = false;
        $data['aurora_reservation_id'] = NULL;

        return $data;
    }

    public function itinerary_service($services_aurora, $itinerary_stela)
    {
        $category = null;
        $aurora_select = null;
        foreach($services_aurora['services'] as $aurora)
        {
            if($aurora->code == trim($itinerary_stela->codsvs))
            {
                $aurora_select = $aurora;
                if(isset($aurora->serviceType)){
                    $category = $aurora->serviceType->translations[0]->value;
                }
                break;
            }
        }

        $data = [];
        $data['file_id'] = $this->file_id;
        $data['entity'] = 'service';
        $data['object_id'] = isset($aurora_select) ?  $aurora_select->id : NULL;
        $data['name'] = isset($aurora_select) ?  $aurora_select->details[0]->name : $itinerary_stela->name;
        $data['category'] = $category;
        $data['object_code'] = $itinerary_stela->codsvs;

        $data['country_in_iso'] = isset($aurora_select) ?  $aurora_select->origin->country->iso : NULL ;
        $data['country_in_name'] = isset($aurora_select) ?  $aurora_select->origin->country->translations[0]->value : NULL;
        $data['city_in_iso'] = isset($aurora_select) ?  $aurora_select->origin->city->iso : NULL;
        $data['city_in_name'] = isset($aurora_select) ?  $aurora_select->origin->city->translations[0]->value : NULL;
        $data['zone_in_iso'] = NULL;
        $data['zone_in_id'] = isset($aurora_select) ?  $aurora_select->origin->zone->id : NULL;
        $data['zone_in_airport'] = NULL;

        $data['country_out_iiso'] = isset($aurora_select) ?  $aurora_select->destination->country->iso : NULL ;
        $data['country_out_iname'] = isset($aurora_select) ?  $aurora_select->destination->country->translations[0]->value : NULL;
        $data['city_out_iiso'] = isset($aurora_select) ?  $aurora_select->destination->city->iso : NULL;
        $data['city_out_iname'] = isset($aurora_select) ?  $aurora_select->destination->city->translations[0]->value : NULL;
        $data['zone_out_iiso'] = NULL;
        $data['zone_out_iid'] = isset($aurora_select) ?  $aurora_select->destination->zone->id : NULL;
        $data['zone_out_iairport'] = NULL;


        $data['start_time'] = $itinerary_stela->horin;
        $data['departure_time'] = $itinerary_stela->horout;
        $data['date_in'] = $itinerary_stela->fecini;
        $data['date_out'] = $itinerary_stela->fecfin;
        $data['total_adults'] = $itinerary_stela->canadl;
        $data['total_children'] = $itinerary_stela->canchd;
        $data['total_infants'] = $itinerary_stela->caninf;
        $data['markup_created'] = NULL; // FALTA RETORNAR
        $data['total_amount'] = $itinerary_stela->totcos;
        $data['total_cost_amount'] = $itinerary_stela->totven;
        $data['serial_sharing'] = 0;
        $data['executive_code'] = 'SBR'; // FALTA RETORNAR
        $data['status'] = 1; //FALTA RETORNAR
        $data['confirmation_status'] = 1; //FALTA RETORNAR
        $data['policies_cancellation_service'] = NULL;
        $data['data_passengers'] = NULL;
        $data['service_rate_id'] = NULL;
        $data['is_in_ope'] = 0; // FALTA RETORNAR
        $data['sent_to_ope'] = 0; // FALTA RETORNAR
        $data['hotel_origin'] = NULL;
        $data['hotel_destination'] = NULL;
        $data['service_supplier_code'] = NULL;
        $data['service_supplier_name'] = NULL;
        $data['protected_rate'] = 0;
        $data['view_protected_rate'] = false;
        $data['service_category_id']  = isset($aurora_select) ?  $aurora_select->service_category_id : NULL ;
        $data['service_sub_category_id']  = isset($aurora_select) ?  $aurora_select->service_sub_category_id: NULL ;
        $data['service_type_id']  = isset($aurora_select) ?  $aurora_select->serviceType->id : NULL ;
        $data['service_summary']  = isset($aurora_select) ?  strip_tags($aurora_select->details[0]->summary) : NULL ;
        $data['service_itinerary']  = isset($aurora_select) ?  strip_tags($aurora_select->details[0]->itinerary) : NULL ;
        $data['add_to_statement'] = false;
        $data['aurora_reservation_id'] = NULL;
        return $data;
    }

    public function itinerary_flight($services_aurora, $itinerary_stela)
    {
        return [
            'id' => null,
            'file_id' => $this->file_id,
            'entity' => 'flight',
            'object_id' => $itinerary_stela->codsvs,
            'name' => $itinerary_stela->codsvs,
            'category' => null,
            'object_code' => $itinerary_stela->codsvs,
            'country_in_iso' => null,
            'country_in_name' => null,
            'city_in_iso' => $itinerary_stela->ciuin,
            'city_in_name' => null,
            'zone_in_iso' => null,
            'country_out_iso' => null,
            'country_out_name' => null,
            'city_out_iso' => $itinerary_stela->ciuout,
            'city_out_name' => null,
            'zone_out_iso' => null,
            'start_time' => null,
            'departure_time' => null,
            'date_in' => $itinerary_stela->fecin,
            'date_out' => $itinerary_stela->fecfin,
            'total_adults' => $itinerary_stela->canadl,
            'total_children' => $itinerary_stela->canchd,
            'total_infants' => $itinerary_stela->caninf,
            'markup_created' => 0,
            'total_amount' => 0,
            'total_cost_amount' => 0,
            'profitability' => 0,
            'serial_sharing' => 0,
            'status' => true,
            'confirmation_status' => true,
            'protected_rate' => 0,
            'view_protected_rate' => false,
            'serial_sharing' => 0,
            'is_in_ope' => 0,
            'sent_to_ope' => 0,
            'hotel_origin' => NULL,
            'hotel_destination' => NULL,
            'rooms' => [],
            'add_to_statement' => true,
            'aurora_reservation_id' => NULL
        ];
    }

    public function getEquivalenceAurora($stela_services): array
    {

        $aurora = new AuroraExternalApiService();

        $results = [
            'hotels' => [],
            'services' => [],
            'flight' => [],
        ];

        foreach($stela_services as $service)
        {
            if($service->tipsvs == 'HTL'){
                array_push($results['hotels'], $service->codsvs);
            }

            if($service->tipsvs == 'SVS'){
                array_push($results['services'], $service->codsvs);
            }
        }

        $params = [
            'services' => $results
        ];


        $aurora_services = (array )$aurora->searchServicesDetailByStela($params);

        return (array) $aurora_services['results'];

    }

    public function searchSerivice($stela_services, $service): array
    {


        return [];

    }


}
