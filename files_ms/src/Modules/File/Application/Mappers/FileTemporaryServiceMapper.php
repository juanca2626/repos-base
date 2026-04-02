<?php

namespace Src\Modules\File\Application\Mappers;

use Illuminate\Http\Request;
use Src\Modules\File\Domain\Model\File;
use Src\Modules\File\Domain\Model\FileItinerary;
use Src\Modules\File\Domain\Model\FileTemporaryService;
use Src\Modules\File\Domain\ValueObjects\File\FileId;
use Src\Modules\File\Domain\ValueObjects\FileTemporaryService\ConfirmationStatus;
use Src\Modules\File\Domain\ValueObjects\FileTemporaryService\Category;
use Src\Modules\File\Domain\ValueObjects\FileTemporaryService\CityInIso;
use Src\Modules\File\Domain\ValueObjects\FileTemporaryService\CityInName;
use Src\Modules\File\Domain\ValueObjects\FileTemporaryService\CityOutName;
use Src\Modules\File\Domain\ValueObjects\FileTemporaryService\CityOutIso;
use Src\Modules\File\Domain\ValueObjects\FileTemporaryService\CountryInName;
use Src\Modules\File\Domain\ValueObjects\FileTemporaryService\CountryOutName;
use Src\Modules\File\Domain\ValueObjects\FileTemporaryService\DateIn;
use Src\Modules\File\Domain\ValueObjects\FileTemporaryService\DateOut;
use Src\Modules\File\Domain\ValueObjects\FileTemporaryService\DepartureTime;
use Src\Modules\File\Domain\ValueObjects\FileTemporaryService\EntityObject; 
use Src\Modules\File\Domain\ValueObjects\FileTemporaryService\MarkupCreated;
use Src\Modules\File\Domain\ValueObjects\FileTemporaryService\Name;
use Src\Modules\File\Domain\ValueObjects\FileTemporaryService\ObjectId;
use Src\Modules\File\Domain\ValueObjects\FileTemporaryService\CountryOutIso;
use Src\Modules\File\Domain\ValueObjects\FileTemporaryService\TotalAdults;
use Src\Modules\File\Domain\ValueObjects\FileTemporaryService\TotalChildren;
use Src\Modules\File\Domain\ValueObjects\FileTemporaryService\TotalInfants;
use Src\Modules\File\Domain\ValueObjects\FileTemporaryService\ZoneOutIso;
use Src\Modules\File\Domain\ValueObjects\FileTemporaryService\SerialSharing;
use Src\Modules\File\Domain\ValueObjects\FileTemporaryService\ServiceCode;
use Src\Modules\File\Domain\ValueObjects\FileTemporaryService\CountryInIso;
use Src\Modules\File\Domain\ValueObjects\FileTemporaryService\CreatedAt; 
use Src\Modules\File\Domain\ValueObjects\FileTemporaryService\ExecutiveCode; 
use Src\Modules\File\Domain\ValueObjects\FileTemporaryService\FileItineraryDetails;
use Src\Modules\File\Domain\ValueObjects\FileTemporaryService\FileServices;
use Src\Modules\File\Domain\ValueObjects\FileTemporaryService\HotelDestination;
use Src\Modules\File\Domain\ValueObjects\FileTemporaryService\HotelOrigin;
use Src\Modules\File\Domain\ValueObjects\FileTemporaryService\IsInOpe; 
use Src\Modules\File\Domain\ValueObjects\FileTemporaryService\PoliciesCancellationService;
use Src\Modules\File\Domain\ValueObjects\FileTemporaryService\Profitability;
use Src\Modules\File\Domain\ValueObjects\FileTemporaryService\SentToOpe; 
use Src\Modules\File\Domain\ValueObjects\FileTemporaryService\ServiceRateId;
use Src\Modules\File\Domain\ValueObjects\FileTemporaryService\StartTime;
use Src\Modules\File\Domain\ValueObjects\FileTemporaryService\Status;
use Src\Modules\File\Domain\ValueObjects\FileTemporaryService\ZoneInIso;
use Src\Modules\File\Domain\ValueObjects\FileTemporaryService\TotalAmount;
use Src\Modules\File\Domain\ValueObjects\FileTemporaryService\TotalCostAmount;
use Src\Modules\File\Domain\ValueObjects\FileTemporaryService\ZoneInAirport;
use Src\Modules\File\Domain\ValueObjects\FileTemporaryService\ZoneInId;
use Src\Modules\File\Domain\ValueObjects\FileTemporaryService\ZoneOutAirport;
use Src\Modules\File\Domain\ValueObjects\FileTemporaryService\ZoneOutId;
use Src\Modules\File\Infrastructure\Persistence\EloquentModel\FileItineraryEloquentModel;
use Src\Modules\File\Infrastructure\Persistence\EloquentModel\FileTemporaryServiceEloquentModel;

class FileTemporaryServiceMapper
{  
    public static function fromRequestCreate(Request $request, array $file): FileTemporaryService 
    {     
 
        $itinerary = $request->toArray();
        // self::validateServiceTemporaryFields($request, $file);   
        $itinerary['adult_num'] = isset($itinerary['adult_num']) ? $itinerary['adult_num'] : $file['adults'];
        $itinerary['child_num'] = isset($itinerary['child_num']) ? $itinerary['child_num'] : $file['children'];
        $itinerary['inf_num'] = isset($itinerary['inf_num']) ? $itinerary['inf_num'] : $file['infants'];     
                
        $serviceTemporary = self::createServiceTemporaryEntity($itinerary, $file['id']);
                
        return FileTemporaryServiceMapper::fromArray($serviceTemporary);    

    }
 
    private static function createServiceTemporaryEntity(array $service, ?int $file_id): array
    {
        
        $details = [];
        foreach($service['details'] as $detail){
            $detail['id'] = null;
            $detail['file_itinerary_id'] = null;
            array_push($details, $detail);
        }
  
        $serviceEntities = [];
        foreach ($service['services'] as $serv) { 
            $serv['status'] = 1;
            $serv['date_out'] = $serv['date_in'];
            $serv['is_in_ope'] = 0;  
            $serv['sent_to_ope'] = 0;  
            $serv['file_service_amount'] =  [
                'id' => NULL,
                'file_amount_type_flag_id' => 1,
                'file_amount_reason_id' => 8,
                'file_service_id' => 0,
                'user_id' => 1,
                'amount_previous' => 0,
                'amount' =>  $serv['amount_cost']
            ];

            foreach($serv['compositions'] as $index => $composition){
                $composition['is_in_ope'] = 0;  
                $composition['sent_to_ope'] = 0;  

                foreach($composition['units'] as $index2 => $unit){
                    $unit['status'] = 1;    
                    $unit['units'][$index2] = $unit;
                    $composition['units'][$index2] = $unit;
                }

                $serv['compositions'][$index] = $composition;
            }
           
            array_push($serviceEntities,$serv);
        }

        return [
            'id' => null,
            'file_id' => $file_id,
            'entity' => 'service-temporary',
            'object_id' => $service['object_id'],
            'name' => $service['name'],
            'category' => $service['category'],
            'object_code' => $service['object_code'],
            'country_in_iso' => $service['country_in_iso'],
            'country_in_name' => $service['country_in_name'],
            'city_in_iso' => $service['city_in_iso'],
            'city_in_name' => $service['city_in_name'],
            'zone_in_iso' => $service['zone_in_iso'],
            'country_out_iso' => $service['country_out_iso'],
            'country_out_name' => $service['country_out_name'],
            'city_out_iso' => $service['city_out_iso'],
            'city_out_name' => $service['city_out_name'],
            'zone_out_iso' => $service['zone_out_iso'],
            'start_time' => $service['start_time'],
            'departure_time' => $service['departure_time'],
            'date_in' => date('Y-m-d'),
            'date_out' => date('Y-m-d'),
            'total_adults' => $service['adult_num'],
            'total_children' => $service['child_num'],
            'total_infants' => $service['inf_num'],
            'markup_created' => $service['markup_created'],
            'total_amount' => $service['total_amount'],
            'total_cost_amount' => $service['total_cost_amount'],
            'profitability' => 0,
            'serial_sharing' => 0,
            'status' => true,
            'confirmation_status' => true,             
            'serial_sharing' => 0,
            'is_in_ope' => 0,  
            'sent_to_ope' => 0,     
            'hotel_origin' => NULL,
            'hotel_destination' => NULL,  
            'services' => $serviceEntities,
            'details'=> $details 
        ];        
    }    

    public static function fromArray(array $serviceTemporary): FileTemporaryService
    {
 
        $fileTemporaryServiceEloquentModel = new FileTemporaryServiceEloquentModel($serviceTemporary);    
        $fileTemporaryServiceEloquentModel->id = $serviceTemporary['id'] ?? null; 
        $fileTemporaryServiceEloquentModel->services = collect(); 
        $fileTemporaryServiceEloquentModel->details = collect(); 
          
        if(isset($serviceTemporary['details'])){
            foreach($serviceTemporary['details'] as $detail) {
                $fileTemporaryServiceEloquentModel->details->add($detail);
            }
        }

        foreach($serviceTemporary['services'] as $service) {
            $fileTemporaryServiceEloquentModel->services->add($service);
        }
      
        return self::fromEloquent($fileTemporaryServiceEloquentModel);
    }

    public static function fromEloquent(FileTemporaryServiceEloquentModel $fileTemporaryServiceEloquentModel): FileTemporaryService
    {
     
        $details = array_map(function ($detail) {
            return FileTemporaryServiceDetailMapper::fromArray($detail);
        }, $fileTemporaryServiceEloquentModel->details?->toArray() ?? []);
  
        $services = array_map(function ($services) {
            return FileTemporaryMasterServiceMapper::fromArray($services);
        }, $fileTemporaryServiceEloquentModel->services?->toArray() ?? []);

        return new FileTemporaryService(
            id: $fileTemporaryServiceEloquentModel->id,
            fileId: new FileId($fileTemporaryServiceEloquentModel->file_id),
            entity: new EntityObject($fileTemporaryServiceEloquentModel->entity),
            objectId: new ObjectId($fileTemporaryServiceEloquentModel->object_id),
            name: new Name($fileTemporaryServiceEloquentModel->name),
            category: new Category($fileTemporaryServiceEloquentModel->category),
            serviceCode: new ServiceCode($fileTemporaryServiceEloquentModel->object_code),
            countryInIso: new CountryInIso($fileTemporaryServiceEloquentModel->country_in_iso),
            countryInName: new CountryInName($fileTemporaryServiceEloquentModel->country_in_name),
            cityInIso: new CityInIso($fileTemporaryServiceEloquentModel->city_in_iso),
            cityInName: new CityInName($fileTemporaryServiceEloquentModel->city_in_name),
            zoneInIso: new ZoneInIso($fileTemporaryServiceEloquentModel->zone_in_iso),
            zoneInId: new ZoneInId($fileTemporaryServiceEloquentModel->zone_in_id),
            zoneInAirport: new ZoneInAirport($fileTemporaryServiceEloquentModel->zone_in_airport),
            countryOutIso: new CountryOutIso($fileTemporaryServiceEloquentModel->country_out_iso),
            countryOutName: new CountryOutName($fileTemporaryServiceEloquentModel->country_out_name),
            cityOutIso: new CityOutIso($fileTemporaryServiceEloquentModel->city_out_iso),
            cityOutName: new CityOutName($fileTemporaryServiceEloquentModel->city_out_name),
            zoneOutIso: new ZoneOutIso($fileTemporaryServiceEloquentModel->zone_out_iso),
            zoneOutId: new ZoneOutId($fileTemporaryServiceEloquentModel->zone_out_id),
            zoneOutAirport: new ZoneOutAirport($fileTemporaryServiceEloquentModel->zone_out_airport),            
            startTime: new StartTime($fileTemporaryServiceEloquentModel->start_time),
            departureTime: new DepartureTime($fileTemporaryServiceEloquentModel->departure_time),
            dateIn: new DateIn($fileTemporaryServiceEloquentModel->date_in),
            dateOut: new DateOut($fileTemporaryServiceEloquentModel->date_out),
            totalAdults: new TotalAdults($fileTemporaryServiceEloquentModel->total_adults),
            totalChildren: new TotalChildren($fileTemporaryServiceEloquentModel->total_children),
            totalInfants: new TotalInfants($fileTemporaryServiceEloquentModel->total_infants),
            markupCreated: new MarkupCreated($fileTemporaryServiceEloquentModel->markup_created),
            totalAmount: new TotalAmount($fileTemporaryServiceEloquentModel->total_amount),
            totalCostAmount : new TotalCostAmount($fileTemporaryServiceEloquentModel->total_cost_amount),
            profitability : new Profitability($fileTemporaryServiceEloquentModel->profitability),
            serialSharing: new SerialSharing($fileTemporaryServiceEloquentModel->serial_sharing),
            executiveCode: new ExecutiveCode($fileTemporaryServiceEloquentModel->executive_code),
            status: new Status($fileTemporaryServiceEloquentModel->status),
            confirmationStatus: new ConfirmationStatus($fileTemporaryServiceEloquentModel->confirmation_status),
            createdAt: new CreatedAt($fileTemporaryServiceEloquentModel->created_at),  
            policiesCancellationService: new PoliciesCancellationService($fileTemporaryServiceEloquentModel->policies_cancellation_service),
            serviceRateId: new ServiceRateId($fileTemporaryServiceEloquentModel->service_rate_id), 
            isInOpe: new IsInOpe($fileTemporaryServiceEloquentModel->is_in_ope),
            sentToOpe: new SentToOpe($fileTemporaryServiceEloquentModel->sent_to_ope),
            hotelOrigin: new HotelOrigin($fileTemporaryServiceEloquentModel->hotel_origin),
            hotelDestination: new HotelDestination($fileTemporaryServiceEloquentModel->hotel_destination), 
            services: new FileServices($services), 
            details: new FileItineraryDetails($details) 
        );
    }

    public static function toEloquent(FileTemporaryService $serviceTemporary): FileTemporaryServiceEloquentModel
    {
        $fileItineraryEloquent = new FileTemporaryServiceEloquentModel();
        if ($serviceTemporary->id) {
            $fileItineraryEloquent = FileTemporaryServiceEloquentModel::query()->findOrFail($serviceTemporary->id);
        }
        $fileItineraryEloquent->file_id = $serviceTemporary->fileId->value();
        $fileItineraryEloquent->entity = $serviceTemporary->entity->value();
        $fileItineraryEloquent->object_id = $serviceTemporary->objectId->value();
        $fileItineraryEloquent->name = $serviceTemporary->name->value();
        $fileItineraryEloquent->category = $serviceTemporary->category->value();
        $fileItineraryEloquent->object_code = $serviceTemporary->serviceCode->value();
        $fileItineraryEloquent->country_in_iso = $serviceTemporary->countryInIso->value();
        $fileItineraryEloquent->country_in_name = $serviceTemporary->countryInName->value();
        $fileItineraryEloquent->city_in_iso = $serviceTemporary->cityInIso->value();
        $fileItineraryEloquent->city_in_name = $serviceTemporary->cityInName->value();
        $fileItineraryEloquent->zone_in_iso = $serviceTemporary->zoneInIso->value();

        $fileItineraryEloquent->zone_in_id = $serviceTemporary->zoneInId->value();
        $fileItineraryEloquent->zone_in_airport = $serviceTemporary->zoneInAirport->value();

        $fileItineraryEloquent->country_out_iso = $serviceTemporary->countryOutIso->value();
        $fileItineraryEloquent->country_out_name = $serviceTemporary->countryOutName->value();
        $fileItineraryEloquent->city_out_iso = $serviceTemporary->cityOutIso->value();
        $fileItineraryEloquent->city_out_name = $serviceTemporary->cityOutName->value();
        $fileItineraryEloquent->zone_out_iso = $serviceTemporary->zoneOutIso->value();

        $fileItineraryEloquent->zone_out_id = $serviceTemporary->zoneOutId->value();
        $fileItineraryEloquent->zone_out_airport = $serviceTemporary->zoneOutAirport->value();

        $fileItineraryEloquent->start_time = $serviceTemporary->startTime->value();
        $fileItineraryEloquent->departure_time = $serviceTemporary->departureTime->value();
        $fileItineraryEloquent->date_in = $serviceTemporary->dateIn->value();
        $fileItineraryEloquent->date_out = $serviceTemporary->dateOut->value();
        $fileItineraryEloquent->total_adults = $serviceTemporary->totalAdults->value();
        $fileItineraryEloquent->total_children = $serviceTemporary->totalChildren->value();
        $fileItineraryEloquent->total_infants = $serviceTemporary->totalInfants->value();
        $fileItineraryEloquent->markup_created = $serviceTemporary->markupCreated->value();
        $fileItineraryEloquent->total_amount = $serviceTemporary->totalAmount->value();
        $fileItineraryEloquent->total_cost_amount = $serviceTemporary->totalCostAmount->value();
        $fileItineraryEloquent->serial_sharing = $serviceTemporary->serialSharing->value();
        $fileItineraryEloquent->executive_code = $serviceTemporary->executiveCode->value();
        $fileItineraryEloquent->status = $serviceTemporary->status->value();
        $fileItineraryEloquent->confirmation_status = $serviceTemporary->confirmationStatus->value(); 
        $fileItineraryEloquent->policies_cancellation_service = $serviceTemporary->policiesCancellationService->value(); 
        $fileItineraryEloquent->service_rate_id = $serviceTemporary->serviceRateId->value();
        $fileItineraryEloquent->is_in_ope = $serviceTemporary->isInOpe->value();
        $fileItineraryEloquent->sent_to_ope = $serviceTemporary->sentToOpe->value();
        $fileItineraryEloquent->hotel_origin = $serviceTemporary->hotelOrigin->value();
        $fileItineraryEloquent->hotel_destination = $serviceTemporary->hotelDestination->value(); 
 
        return $fileItineraryEloquent;
    }

    public static function fromRequestUpdateSchedule(Request $request): array
    {

        $new_time_start = $request->input('start_time', null);
        $new_time_end = $request->input('departure_time', null);

        if($new_time_start === null){
            throw new \DomainException("null hours");
        }

        return [
            'start_time' => new StartTime($new_time_start),
            'departure_time' => new DepartureTime($new_time_end),
        ];

        // $new_time = $request->input('new_time', date("H:i:s"));
        // return new StartTime($new_time);
    }

    public static function fromRequestUpdatePassengers(Request $request): array
    {
        return $request->input('passengers', []);
    }
    
    public static function fromRequestUpdateAmountCost(Request $request): array
    {
        $file_amount_reason_id = $request->input('file_amount_reason_id', 0);
        $file_amount_type_flag_id = $request->input('file_amount_type_flag_id', 0);
        $amount_sale = $request->input('new_amount_sale', 0);
         
        return [
            'file_amount_reason_id' => $file_amount_reason_id,
            'file_amount_type_flag_id' => $file_amount_type_flag_id,
            'amount_sale' => $amount_sale,
        ];
    }

}
