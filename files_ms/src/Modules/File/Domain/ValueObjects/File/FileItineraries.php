<?php

namespace Src\Modules\File\Domain\ValueObjects\File;


use Src\Modules\File\Domain\Model\FileItinerary;
use Src\Shared\Domain\ValueObjects\ValueObjectArray;

final class FileItineraries extends ValueObjectArray
{
    public readonly array $fileItineraries;

    public function __construct(array $fileItineraries)
    {
        parent::__construct($fileItineraries);

        $this->fileItineraries = array_values($fileItineraries);
    }

    public function add(FileItinerary $fileItineraries): void
    {
        $this->append($fileItineraries);
    }

    public function getItineraries(): FileItineraries
    {
        return new FileItineraries($this->fileItineraries);
    }

    public function toArray(): array
    {
        // return $this->fileItineraries;

        $fileItineraries = [];

        foreach($this->fileItineraries as $index => $fileItinerary) {
            $fileItineraryArray = $fileItinerary->toArray();
            // $fileItineraryArray['penalty'] = $fileItinerary->getPenalty();
            array_push($fileItineraries,$fileItineraryArray);
        }

        return $fileItineraries;
    }

    /**
     * @return array
     */
    public function jsonSerialize(): array
    {
        return $this->fileItineraries;
    }
}
