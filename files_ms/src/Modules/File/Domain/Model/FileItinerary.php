<?php

namespace Src\Modules\File\Domain\Model;

use Src\Modules\File\Domain\ValueObjects\FileItinerary\CityInName;
use Src\Modules\File\Domain\ValueObjects\FileItinerary\CountryInName;
use Src\Modules\File\Domain\ValueObjects\FileItinerary\CountryOutName;
use Src\Modules\File\Domain\ValueObjects\FileItinerary\DateIn;
use Src\Modules\File\Domain\ValueObjects\FileItinerary\DateOut;
use Src\Modules\File\Domain\ValueObjects\File\FileId;
use Src\Modules\File\Domain\ValueObjects\FileItinerary\Category;
use Src\Modules\File\Domain\ValueObjects\FileItinerary\CityInIso;
use Src\Modules\File\Domain\ValueObjects\FileItinerary\CityOutName;
use Src\Modules\File\Domain\ValueObjects\FileItinerary\CityOutIso;
use Src\Modules\File\Domain\ValueObjects\FileItinerary\DepartureTime;
use Src\Modules\File\Domain\ValueObjects\FileItinerary\EntityObject;
use Src\Modules\File\Domain\ValueObjects\FileItinerary\FileItineraryDescriptions;
use Src\Modules\File\Domain\ValueObjects\FileItinerary\FileItineraryFlights;
use Src\Modules\File\Domain\ValueObjects\FileItinerary\FileHotelRooms;
use Src\Modules\File\Domain\ValueObjects\FileItinerary\MarkupCreated;
use Src\Modules\File\Domain\ValueObjects\FileItinerary\Name;
use Src\Modules\File\Domain\ValueObjects\FileItinerary\ObjectId;
use Src\Modules\File\Domain\ValueObjects\FileItinerary\CountryOutIso;
use Src\Modules\File\Domain\ValueObjects\FileItinerary\TotalAdults;
use Src\Modules\File\Domain\ValueObjects\FileItinerary\TotalChildren;
use Src\Modules\File\Domain\ValueObjects\FileItinerary\TotalInfants;
use Src\Modules\File\Domain\ValueObjects\FileItinerary\ZoneOutIso;
use Src\Modules\File\Domain\ValueObjects\FileItinerary\SerialSharing;
use Src\Modules\File\Domain\ValueObjects\FileItinerary\ServiceCode;
use Src\Modules\File\Domain\ValueObjects\FileItinerary\CountryInIso;
use Src\Modules\File\Domain\ValueObjects\FileItinerary\DataPassengers;
use Src\Modules\File\Domain\ValueObjects\FileItinerary\ExecutiveCode;
use Src\Modules\File\Domain\ValueObjects\FileItinerary\FileItinearyRoomAmountLogs;
use Src\Modules\File\Domain\ValueObjects\FileItinerary\FileItinearyServiceAmountLogs;
use Src\Modules\File\Domain\ValueObjects\FileItinerary\FileServices;
use Src\Modules\File\Domain\ValueObjects\FileItinerary\Profitability;
use Src\Modules\File\Domain\ValueObjects\FileItinerary\StartTime;
use Src\Modules\File\Domain\ValueObjects\FileItinerary\ZoneInIso;
use Src\Modules\File\Domain\ValueObjects\FileItinerary\TotalAmount;
use Src\Modules\File\Domain\ValueObjects\FileItinerary\TotalCostAmount;
use Src\Modules\File\Domain\ValueObjects\FileItinerary\ConfirmationStatus;
use Src\Modules\File\Domain\ValueObjects\FileItinerary\CreatedAt;
use Src\Modules\File\Domain\ValueObjects\FileItinerary\PoliciesCancellationService;
use Src\Modules\File\Domain\ValueObjects\FileItinerary\ServiceRateId;
use Src\Modules\File\Domain\ValueObjects\FileItinerary\Status;
use Src\Shared\Domain\Entity;
use Carbon\Carbon;
use Src\Modules\File\Domain\ValueObjects\FileItinerary\AddToStatement;
use Src\Modules\File\Domain\ValueObjects\FileItinerary\AuroraReservationId;
use Src\Modules\File\Domain\ValueObjects\FileItinerary\FileItineraryAccommodations;
use Src\Modules\File\Domain\ValueObjects\FileItinerary\FileItineraryDetails;
use Src\Modules\File\Domain\ValueObjects\FileItinerary\FilesMsParameters;
use Src\Modules\File\Domain\ValueObjects\FileItinerary\FrecuencyCode;
use Src\Modules\File\Domain\ValueObjects\FileItinerary\HotelDestination;
use Src\Modules\File\Domain\ValueObjects\FileItinerary\HotelOrigin;
use Src\Modules\File\Domain\ValueObjects\FileItinerary\IsInOpe;
use Src\Modules\File\Domain\ValueObjects\FileItinerary\ProtectedRate;
use Src\Modules\File\Domain\ValueObjects\FileItinerary\SentToOpe;
use Src\Modules\File\Domain\ValueObjects\FileItinerary\ServiceCategoryId;
use Src\Modules\File\Domain\ValueObjects\FileItinerary\ServiceItinerary; 
use Src\Modules\File\Domain\ValueObjects\FileItinerary\ServiceSubCategoryId;
use Src\Modules\File\Domain\ValueObjects\FileItinerary\ServiceSummary;
use Src\Modules\File\Domain\ValueObjects\FileItinerary\ServiceSupplierCode;
use Src\Modules\File\Domain\ValueObjects\FileItinerary\ServiceSupplierName;
use Src\Modules\File\Domain\ValueObjects\FileItinerary\ServiceTypeId;
use Src\Modules\File\Domain\ValueObjects\FileItinerary\ViewProtectedRate;
use Src\Modules\File\Domain\ValueObjects\FileItinerary\ZoneInAirport;
use Src\Modules\File\Domain\ValueObjects\FileItinerary\ZoneInId;
use Src\Modules\File\Domain\ValueObjects\FileItinerary\ZoneOutAirport;
use Src\Modules\File\Domain\ValueObjects\FileItinerary\ZoneOutId;

class FileItinerary extends Entity
{

    public function __construct(
        public ?int $id,
        public readonly ?FileId $fileId,
        public readonly EntityObject $entity,
        public readonly ObjectId $objectId,
        public readonly Name $name,
        public readonly Category $category,
        public readonly ServiceCode $serviceCode,
        public readonly CountryInIso $countryInIso,
        public readonly CountryInName $countryInName,
        public readonly CityInIso $cityInIso,
        public readonly CityInName $cityInName,
        public readonly ZoneInIso $zoneInIso,
        public readonly ZoneInId $zoneInId,
        public readonly ZoneInAirport $zoneInAirport,
        public readonly CountryOutIso $countryOutIso,
        public readonly CountryOutName $countryOutName,
        public readonly CityOutIso $cityOutIso,
        public readonly CityOutName $cityOutName,
        public readonly ZoneOutIso $zoneOutIso,
        public readonly ZoneOutId $zoneOutId,
        public readonly ZoneOutAirport $zoneOutAirport,        
        public readonly StartTime $startTime,
        public readonly DepartureTime $departureTime,
        public readonly DateIn $dateIn,
        public readonly DateOut $dateOut,
        public readonly TotalAdults $totalAdults,
        public readonly TotalChildren $totalChildren,
        public readonly TotalInfants $totalInfants,
        public readonly MarkupCreated $markupCreated,
        public readonly TotalAmount $totalAmount,
        public readonly TotalCostAmount $totalCostAmount,
        public readonly Profitability $profitability,
        public readonly SerialSharing $serialSharing,
        public readonly ExecutiveCode $executiveCode,
        public readonly Status $status,
        public readonly ConfirmationStatus $confirmationStatus,
        public readonly CreatedAt $createdAt,
        public readonly DataPassengers $dataPassengers,
        public readonly FileItineraryFlights $flights,
        public readonly FileItineraryDescriptions $descriptions,
        public readonly PoliciesCancellationService $policiesCancellationService,
        public readonly ServiceRateId $serviceRateId,
        public readonly IsInOpe $isInOpe, 
        public readonly SentToOpe $sentToOpe,  
        public readonly HotelOrigin $hotelOrigin, 
        public readonly HotelDestination $hotelDestination, 
 
        // public readonly FileHotelRooms $hotelsRooms, 
        public readonly FileHotelRooms $rooms,
        public readonly FileServices $services,
        public readonly FileItinearyServiceAmountLogs $serviceAmountLogs,
        public readonly FileItinearyRoomAmountLogs $roomAmountLogs,
        public readonly ServiceSupplierCode $serviceSupplierCode,
        public readonly ServiceSupplierName $serviceSupplierName,
        public readonly FileItineraryDetails $details,
        public readonly FileItineraryAccommodations $accommodations,
        public readonly ProtectedRate $protectedRate,
        public readonly ViewProtectedRate $viewProtectedRate, 
        public readonly ServiceCategoryId $serviceCategoryId,
        public readonly ServiceSubCategoryId $serviceSubCategoryId,        
        public readonly ServiceTypeId $serviceTypeId,
        public readonly ServiceSummary $serviceSummary,
        public readonly ServiceItinerary $serviceItinerary ,
        public readonly AddToStatement $addToStatement, 
        public readonly AuroraReservationId $auroraReservationId,
        public readonly FilesMsParameters $filesMsParameters
 
    ) {
    }
 
    public function getFileHotelRooms(): ?FileHotelRooms
    {
        return $this->rooms->getFileHotelRooms();
    }

    public function getFileServices(): ?FileServices
    {
        return $this->services->getFileServices();
    }

    public function getFileItinearyServiceAmountLogs(): ?FileItinearyServiceAmountLogs
    {
        return $this->serviceAmountLogs->getFileServices();
    }

    public function getFileItinearyRoomAmountLogs(): ?FileItinearyRoomAmountLogs
    {
        return $this->roomAmountLogs->getFileServices();
    }

    public function getFileItineraryDetails(): ?FileItineraryDetails
    {
        return $this->details->getItineraryDetails();
    }

    public function getItineraryAccommodations(): ?FileItineraryAccommodations
    {
        return $this->accommodations->getItineraryAccommodations();
    }

    public function nights(): int
    {
        $date1 = new \DateTime($this->dateIn->value());
        $date2 = new \DateTime($this->dateOut->value());
        $diff = $date1->diff($date2);

        return $diff->days;
    }
    
    /**
     * @return array
     */
    public function toArray(): array
    {
        $result = [
            'id' => $this->id,
            'file_id' => $this->fileId->value(),
            'entity' => $this->entity->value(),
            'object_id' => $this->objectId->value(),
            'name' => $this->name->value(),
            'category' => $this->category->value(),
            'object_code' => $this->serviceCode->value(),
            'country_in_iso' => $this->countryInIso->value(),
            'country_in_name' => $this->countryInName->value(),
            'city_in_iso' => $this->cityInIso->value(),
            'city_in_name' => $this->cityInName->value(),
            'zone_in_iso' => $this->zoneInIso->value(),
            'zone_in_id' => $this->zoneInId->value(),
            'zone_in_airport' => $this->zoneInAirport->value(),            
            'country_out_iso' => $this->countryOutIso->value(),
            'country_out_name' => $this->countryOutName->value(),
            'city_out_iso' => $this->cityOutIso->value(),
            'city_out_name' => $this->cityOutName->value(),
            'zone_out_iso' => $this->zoneOutIso->value(),
            'zone_out_id' => $this->zoneOutId->value(),
            'zone_out_airport' => $this->zoneOutAirport->value(),            
            'start_time' => $this->startTime->value(),
            'departure_time' => $this->departureTime->value(),            
            'date_in' => $this->dateIn->value(),
            'date_out' => $this->dateOut->value(),
            'total_adults' => $this->totalAdults->value(),
            'total_children' => $this->totalChildren->value(),
            'total_infants' => $this->totalInfants->value(),
            'markup_created' => $this->markupCreated->value(),
            'total_amount' => $this->totalAmount->value(),
            'total_cost_amount' => $this->totalCostAmount->value(),
            'profitability' => $this->profitability->value(),
            'serial_sharing' => $this->serialSharing->value(),
            'executive_code' => $this->executiveCode->value(),
            'status' => $this->status->value(),
            'confirmation_status' => $this->confirmationStatus->value(),
            'policies_cancellation_service' => $this->policiesCancellationService->value(),
            'service_rate_id' => $this->serviceRateId->value(),
            'created_at' => $this->createdAt->value(),
            'nights' =>  $this->nights(),
            'is_in_ope' =>  $this->isInOpe->value(),
            'sent_to_ope' =>  $this->sentToOpe->value(),
            'hotel_origin' =>  $this->hotelOrigin->value(),
            'hotel_destination' =>  $this->hotelDestination->value(),
            'protected_rate' =>  $this->protectedRate->value(),
            'view_protected_rate' => $this->viewProtectedRate->value(),   
            'add_to_statement' => $this->addToStatement->value(),
            'aurora_reservation_id' => $this->auroraReservationId->value(),
            'files_ms_parameters' => $this->filesMsParameters->value(),                        
        ];

        if($this->entity->value() == 'hotel') {
            $result['room_amount_logs'] = $this->getFileItinearyRoomAmountLogs();
            $result['rooms'] = $this->getFileHotelRooms();
        }

        if(in_array($this->entity->value(), ['service' , 'service-temporary'] )) {
            
            $result['service_category_id'] =  $this->serviceCategoryId->value();
            $result['service_sub_category_id'] =  $this->serviceSubCategoryId->value();
            $result['service_type_id'] =  $this->serviceTypeId->value();
            $result['service_summary'] = $this->serviceSummary->value();
            $result['service_itinerary'] = $this->serviceItinerary->value();

            $result['service_amount_logs'] = $this->getFileItinearyServiceAmountLogs();
            $result['services'] = $this->getFileServices();
        }
        
        if($this->entity->value() == 'service-mask') {
            $result['service_supplier_code'] = $this->serviceSupplierCode->value();
            $result['service_supplier_name'] = $this->serviceSupplierName->value();
            $result['details'] = $this->getFileItineraryDetails();
            $result['accommodations'] = $this->getItineraryAccommodations();
        }
        

        return $result;
    }

    public function calculatePenalty_BK(): array
    {
 
        $penalidad = [
            'codigoReserva' => 0, //por definir
            'idDetalleSvs' => 0, //por definir
            'totalCosto' => 0,
            'totalIgv' => 0,
            'totalVenta' => 0,
            'tipoCancel' => 'CANCEL'
        ];

        $igv = [
            'percent' => 0,
            'total_amount' => 0,
        ];
       
        $policies_cancellation = json_decode($this->policiesCancellationService->value(), true);
        if($policies_cancellation and count($policies_cancellation)>0){
            $policy_cancellation = (object)$policies_cancellation[0];
            $_apply_date = explode('-', $policy_cancellation->apply_date);
            $apply_date = $_apply_date[2].'-'.$_apply_date[1].'-'.$_apply_date[0];
            // 2021-08-05 < hoy (hoy quieres cancelar, cuando la fecha ya pasó) && ['onRequest'] != 0 (osea es OK) && no fue actualizado el ok desde stella
            if ($apply_date < date("Y-m-d") && $this->confirmationStatus->value() != 0) // $this->status->value() != 0 && $this->confirmationStatus->value() == 0
            {         
                $penalty_price = str_replace(',', '', $policy_cancellation->penalty_price);
                // bug aqui "penalty_price" esta trayendo formateado 1,000.00 cuando son miles y generar errores con suma por es se elimino todas las ","
                $penalty_price = round($penalty_price, 2);
                $igv = round($igv['total_amount'], 2);
    
                $penalidad['totalCosto'] =  $penalty_price;
                $penalidad['totalIgv'] = $igv;
                $penalidad['totalVenta'] = $igv + $penalty_price;
                $penalidad['tipoCancel'] = 'GASCAN';
                
            }
        }
 
        return $penalidad ;
    }
}
