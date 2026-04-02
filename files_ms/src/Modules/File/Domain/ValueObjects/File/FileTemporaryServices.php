<?php

namespace Src\Modules\File\Domain\ValueObjects\File;


use Src\Modules\File\Domain\Model\FileTemporaryService;
use Src\Shared\Domain\ValueObjects\ValueObjectArray;

final class FileTemporaryServices extends ValueObjectArray
{
    public readonly array $fileTemporaryServices;

    public function __construct(array $fileTemporaryServices)
    {
        parent::__construct($fileTemporaryServices);

        $this->fileTemporaryServices = array_values($fileTemporaryServices);
    }

    public function add(FileTemporaryService $fileTemporaryServices): void
    {
        $this->append($fileTemporaryServices);
    }

    public function getItineraries(): FileTemporaryServices
    {
        return new FileTemporaryServices($this->fileTemporaryServices);
    }

    public function toArray(): array
    {
        
        $fileItineraries = [];

        foreach($this->fileTemporaryServices as $index => $fileItinerary) {
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
        return $this->fileTemporaryServices;
    }
}
