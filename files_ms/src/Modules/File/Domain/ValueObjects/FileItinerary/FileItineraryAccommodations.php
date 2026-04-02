<?php

namespace Src\Modules\File\Domain\ValueObjects\FileItinerary;

 
use Src\Shared\Domain\ValueObjects\ValueObjectArray;

final class FileItineraryAccommodations extends ValueObjectArray
{
    public readonly array $fileItineraryAccommodations;

    public function __construct(array $fileItineraryAccommodations)
    {
        parent::__construct($fileItineraryAccommodations);

        $this->fileItineraryAccommodations = array_values($fileItineraryAccommodations);
    }

    public function add(FileItineraryAccommodations $fileItineraryAccommodations): void
    {
        $this->append($fileItineraryAccommodations);
    }

    public function getItineraryAccommodations(): FileItineraryAccommodations
    {
        return new FileItineraryAccommodations($this->fileItineraryAccommodations);
    }

    public function toArray(): array
    {
        return $this->fileItineraryAccommodations;
    }

    /**
     * @return array
     */
    public function jsonSerialize(): array
    {
        return $this->fileItineraryAccommodations;
    }
}
