<?php

namespace Src\Modules\File\Domain\Model;

use Src\Modules\File\Domain\ValueObjects\FileItinerary\FileItineraryId;
use Src\Modules\File\Domain\ValueObjects\FileItineraryFlight\AirlineCode;
use Src\Modules\File\Domain\ValueObjects\FileItineraryFlight\AirlineName;
use Src\Modules\File\Domain\ValueObjects\FileItineraryFlight\AirlineNumber;
use Src\Modules\File\Domain\ValueObjects\FileItineraryFlight\ArrivalTime;
use Src\Modules\File\Domain\ValueObjects\FileItineraryFlight\DepartureTime;
use Src\Modules\File\Domain\ValueObjects\FileItineraryFlight\FileItineraryFlightAccommodations;
use Src\Modules\File\Domain\ValueObjects\FileItineraryFlight\NroPax;
use Src\Modules\File\Domain\ValueObjects\FileItineraryFlight\Pnr;
use Src\Shared\Domain\Entity;

class FileItineraryFlight extends Entity
{

    public function __construct(
        public readonly ?int $id,
        public readonly FileItineraryId $fileItineraryId,
        public readonly AirlineName $airlineName,
        public readonly AirlineCode $airlineCode,
        public readonly AirlineNumber $airlineNumber,
        public readonly Pnr $pnr,
        public readonly DepartureTime $departureTime,
        public readonly ArrivalTime $arrivalTime,        
        public readonly NroPax $nroPax,
        public readonly FileItineraryFlightAccommodations  $accommodations
    ) {
    }

    /**
     * @return array
     */
    public function toArray(): array
    {
        // TODO: Implement toArray() method.
    }

}
