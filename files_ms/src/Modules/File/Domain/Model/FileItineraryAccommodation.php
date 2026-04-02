<?php

namespace Src\Modules\File\Domain\Model;

use Src\Modules\File\Domain\ValueObjects\FileItinerary\FileItineraryId; 
use Src\Modules\File\Domain\ValueObjects\FileItineraryAccommodation\FilePassengerId;
use Src\Modules\File\Domain\ValueObjects\FileItineraryAccommodation\FilePassenger;
use Src\Shared\Domain\Entity;

class FileItineraryAccommodation extends Entity
{

    public function __construct(
        public readonly ?int $id,
        public readonly FileItineraryId $fileItineraryId,
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
            'file_itinerary_id' => $this->fileItineraryId->value(),
            'file_passenger_id' => $this->filePassengerId->value(), 
            'file_passenger' => $this->filePassenger->jsonSerialize()
        ];
    }

}
