<?php
 
namespace Src\Modules\File\Domain\ValueObjects\FileItineraryAccommodation;


use Src\Shared\Domain\ValueObject;

final class FilePassenger extends ValueObject
{
    public readonly object $filePassenger;

    public function __construct(object $filePassenger)
    { 
        $this->filePassenger = $filePassenger;
    }

    public function getFilePassenger(): FilePassenger
    {
        return new FilePassenger($this->filePassenger);
    }

    public function toArray(): object
    {
        return $this->filePassenger;
    }

    /**
     * @return object
     */
    public function jsonSerialize(): object
    {
        return $this->filePassenger;
    }
}
