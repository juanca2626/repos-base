<?php

namespace Src\Modules\File\Domain\ValueObjects\FileItinerary;

use Src\Shared\Domain\ValueObjects\ValueObjectArray;

final class FileItinearyServiceAmountLogs extends ValueObjectArray
{
    public readonly array $fileItineraryServices;

    public function __construct(array $fileItineraryServices)
    {
        parent::__construct($fileItineraryServices);
        $this->fileItineraryServices = array_values($fileItineraryServices);
    }

    public function getFileServices(): FileItinearyServiceAmountLogs
    {
        return new FileItinearyServiceAmountLogs($this->fileItineraryServices);
    }

    public function toArray(): array
    {
        return $this->fileItineraryServices;
    }

    /**
     * @return array
     */
    public function jsonSerialize(): array
    {
        return $this->fileItineraryServices;
    }
}
