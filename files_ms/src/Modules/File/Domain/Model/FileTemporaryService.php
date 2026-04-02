<?php

namespace Src\Modules\File\Domain\Model;

use Src\Modules\File\Domain\ValueObjects\FileTemporaryService\CityInName;
use Src\Modules\File\Domain\ValueObjects\FileTemporaryService\CountryInName;
use Src\Modules\File\Domain\ValueObjects\FileTemporaryService\CountryOutName;
use Src\Modules\File\Domain\ValueObjects\FileTemporaryService\DateIn;
use Src\Modules\File\Domain\ValueObjects\FileTemporaryService\DateOut;
use Src\Modules\File\Domain\ValueObjects\File\FileId;
use Src\Modules\File\Domain\ValueObjects\FileTemporaryService\Category;
use Src\Modules\File\Domain\ValueObjects\FileTemporaryService\CityInIso;
use Src\Modules\File\Domain\ValueObjects\FileTemporaryService\CityOutName;
use Src\Modules\File\Domain\ValueObjects\FileTemporaryService\CityOutIso;
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
use Src\Modules\File\Domain\ValueObjects\FileTemporaryService\ExecutiveCode; 
use Src\Modules\File\Domain\ValueObjects\FileTemporaryService\FileServices;
use Src\Modules\File\Domain\ValueObjects\FileTemporaryService\Profitability;
use Src\Modules\File\Domain\ValueObjects\FileTemporaryService\StartTime;
use Src\Modules\File\Domain\ValueObjects\FileTemporaryService\ZoneInIso;
use Src\Modules\File\Domain\ValueObjects\FileTemporaryService\TotalAmount;
use Src\Modules\File\Domain\ValueObjects\FileTemporaryService\TotalCostAmount;
use Src\Modules\File\Domain\ValueObjects\FileTemporaryService\ConfirmationStatus;
use Src\Modules\File\Domain\ValueObjects\FileTemporaryService\CreatedAt;
use Src\Modules\File\Domain\ValueObjects\FileTemporaryService\PoliciesCancellationService;
use Src\Modules\File\Domain\ValueObjects\FileTemporaryService\ServiceRateId;
use Src\Modules\File\Domain\ValueObjects\FileTemporaryService\Status;
use Src\Shared\Domain\Entity;
use Carbon\Carbon; 
use Src\Modules\File\Domain\ValueObjects\FileTemporaryService\FileItineraryDetails;
use Src\Modules\File\Domain\ValueObjects\FileTemporaryService\HotelDestination;
use Src\Modules\File\Domain\ValueObjects\FileTemporaryService\HotelOrigin;
use Src\Modules\File\Domain\ValueObjects\FileTemporaryService\IsInOpe;
use Src\Modules\File\Domain\ValueObjects\FileTemporaryService\SentToOpe; 
use Src\Modules\File\Domain\ValueObjects\FileTemporaryService\ZoneInAirport;
use Src\Modules\File\Domain\ValueObjects\FileTemporaryService\ZoneInId;
use Src\Modules\File\Domain\ValueObjects\FileTemporaryService\ZoneOutAirport;
use Src\Modules\File\Domain\ValueObjects\FileTemporaryService\ZoneOutId;

class FileTemporaryService extends Entity
{

    public function __construct(
        public readonly ?int $id,
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
        public readonly PoliciesCancellationService $policiesCancellationService,
        public readonly ServiceRateId $serviceRateId,
        public readonly IsInOpe $isInOpe, 
        public readonly SentToOpe $sentToOpe,  
        public readonly HotelOrigin $hotelOrigin, 
        public readonly HotelDestination $hotelDestination,   
        public readonly FileServices $services,    
        public readonly FileItineraryDetails $details, 
 
    ) {
    }
    
    public function getFileServices(): ?FileServices
    {
        return $this->services->getFileServices();
    }
  
    public function getFileItineraryDetails(): ?FileItineraryDetails
    {
        return $this->details->getItineraryDetails();
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
            'services' =>  $this->getFileServices(),
            'details' => $this->details
        ];
  

        return $result;
    }
 
}
