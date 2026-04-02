<?php

namespace Src\Modules\File\Domain\ValueObjects\FileItinerary;


use Src\Modules\File\Domain\Model\FileItineraryFlight;
use Src\Shared\Domain\ValueObjects\ValueObjectArray;

final class FileItineraryFlights extends ValueObjectArray
{
    public readonly array $fileItineraryFlights;

    public function __construct(array $fileItineraryFlights)
    {
        parent::__construct($fileItineraryFlights);

        $this->fileItineraryFlights = array_values($fileItineraryFlights);
    }

    public function add(FileItineraryFlight $fileItineraries): void
    {
        $this->append($fileItineraries);
    }

    public function getItineraryFlights(): FileItineraryFlights
    {
        return new FileItineraryFlights($this->fileItineraryFlights);
    }

    public function toArray(): array
    {
        return $this->fileItineraryFlights;
    }

    /**
     * @return array
     */
    public function jsonSerialize(): array
    {
        return $this->fileItineraryFlights;
    }
}
