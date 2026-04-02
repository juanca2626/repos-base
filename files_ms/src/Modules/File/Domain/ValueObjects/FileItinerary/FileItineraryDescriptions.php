<?php

namespace Src\Modules\File\Domain\ValueObjects\FileItinerary;


use Src\Modules\File\Domain\Model\FileItineraryDescription;
use Src\Modules\File\Domain\Model\FileItineraryFlight;
use Src\Shared\Domain\ValueObjects\ValueObjectArray;

final class FileItineraryDescriptions extends ValueObjectArray
{
    public readonly array $fileItineraryDescriptions;

    public function __construct(array $fileItineraryDescriptions)
    {
        parent::__construct($fileItineraryDescriptions);

        $this->fileItineraryDescriptions = array_values($fileItineraryDescriptions);
    }

    public function add(FileItineraryDescription $fileItineraryDescriptions): void
    {
        $this->append($fileItineraryDescriptions);
    }

    public function getItineraryFlights(): FileItineraryDescriptions
    {
        return new FileItineraryDescriptions($this->fileItineraryDescriptions);
    }

    public function toArray(): array
    {
        return $this->fileItineraryDescriptions;
    }

    /**
     * @return array
     */
    public function jsonSerialize(): array
    {
        return $this->fileItineraryDescriptions;
    }
}
