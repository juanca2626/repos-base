<?php

namespace Src\Modules\File\Domain\ValueObjects\FileHotelRoomUnit;

use Src\Shared\Domain\ValueObjects\IntOrNullValueObject;

final class FileHotelRoomUnitId extends IntOrNullValueObject
{
    public function __construct(int|null $fileHotelRoomUnitId)
    {
        parent::__construct($fileHotelRoomUnitId);
    }
}
