<?php

namespace Src\Modules\File\Domain\Model;

use Src\Modules\File\Domain\ValueObjects\FileItineraryFlight\FileItineraryFlightId;
use Src\Modules\File\Domain\ValueObjects\FilePassenger\FilePassengerId;
use Src\Modules\File\Domain\ValueObjects\FileItineraryFlightAccomodation\FilePassenger; 
use Src\Shared\Domain\Entity;

class FileItineraryFlightAccomodation extends Entity
{
    public function __construct(
        public readonly ?int $id, 
        public readonly FileItineraryFlightId $fileItineraryFlightId,
        public readonly FilePassengerId $filePassengerId, 
        public readonly FilePassenger $filePassenger
    ) {
    }

    /**
     * @return array
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'file_itinerary_flight_id' => $this->fileItineraryFlightId->value(),
            'file_passenger_id' => $this->filePassengerId->value(), 
            'file_passenger' => $this->filePassenger->jsonSerialize()
        ];
    }

}
