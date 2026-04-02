<?php

namespace Src\Modules\File\Domain\ValueObjects\FileTemporaryService;


use Src\Modules\File\Domain\Model\FileTemporaryServiceDetail;
use Src\Shared\Domain\ValueObjects\ValueObjectArray;

final class FileItineraryDetails extends ValueObjectArray
{
    public readonly array $fileItineraryDetails;

    public function __construct(array $fileItineraryDetails)
    {
        parent::__construct($fileItineraryDetails);

        $this->fileItineraryDetails = array_values($fileItineraryDetails);
    }

    public function add(FileTemporaryServiceDetail $fileItineraryDetail): void
    {
        $this->append($fileItineraryDetail);
    }

    public function getItineraryDetails(): FileItineraryDetails
    {
        return new FileItineraryDetails($this->fileItineraryDetails);
    }

    public function toArray(): array
    {
        return $this->fileItineraryDetails;
    }

    /**
     * @return array
     */
    public function jsonSerialize(): array
    {
        return $this->fileItineraryDetails;
    }
}
