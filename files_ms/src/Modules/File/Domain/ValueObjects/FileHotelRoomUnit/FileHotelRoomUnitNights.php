<?php

namespace Src\Modules\File\Domain\ValueObjects\FileHotelRoomUnit;


use Src\Shared\Domain\ValueObjects\ValueObjectArray;

final class FileHotelRoomUnitNights extends ValueObjectArray
{
    public readonly array $fileHotelRoomUnitNights;

    public function __construct(array $fileHotelRoomUnitNights)
    {
        parent::__construct($fileHotelRoomUnitNights);

        $this->fileHotelRoomUnitNights = array_values($fileHotelRoomUnitNights);
    }

    public function getFileHotelRoomNights(): FileHotelRoomUnitNights
    {
        return new FileHotelRoomUnitNights($this->fileHotelRoomUnitNights);
    }

    public function toArray(): array
    {
        return $this->fileHotelRoomUnitNights;
    }

    /**
     * @return array
     */
    public function jsonSerialize(): array
    {
        return $this->fileHotelRoomUnitNights;
    }
}
