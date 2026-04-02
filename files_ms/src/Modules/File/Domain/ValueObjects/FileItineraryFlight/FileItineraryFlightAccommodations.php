<?php

namespace Src\Modules\File\Domain\ValueObjects\FileItineraryFlight;


use Src\Shared\Domain\ValueObjects\ValueObjectArray;

final class FileItineraryFlightAccommodations extends ValueObjectArray
{
    public readonly array $fileItineraryFlightAccommodations;

    public function __construct(array $fileItineraryFlightAccommodations)
    {
        parent::__construct($fileItineraryFlightAccommodations);

        $this->fileItineraryFlightAccommodations = array_values($fileItineraryFlightAccommodations);
    }

    public function getFileItineraryFlightAccommodations(): FileItineraryFlightAccommodations
    {
        return new FileItineraryFlightAccommodations($this->fileItineraryFlightAccommodations);
    }

    public function toArray(): array
    {
        return $this->fileItineraryFlightAccommodations;
    }

    /**
     * @return array
     */
    public function jsonSerialize(): array
    {
        return $this->fileItineraryFlightAccommodations;
    }
}
