<?php

namespace Src\Modules\File\Domain\ValueObjects\FileHotelRoomUnit;


use Src\Shared\Domain\ValueObjects\ValueObjectArray;

final class FileRoomAccommodations extends ValueObjectArray
{
    public readonly array $fileRoomAccommodations;

    public function __construct(array $fileRoomAccommodations)
    {
        parent::__construct($fileRoomAccommodations);

        $this->fileRoomAccommodations = array_values($fileRoomAccommodations);
    }

    public function getFileRoomAccommodations(): FileRoomAccommodations
    {
        return new FileRoomAccommodations($this->fileRoomAccommodations);
    }

    public function toArray(): array
    {
        return $this->fileRoomAccommodations;
    }

    /**
     * @return array
     */
    public function jsonSerialize(): array
    {
        return $this->fileRoomAccommodations;
    }
}
