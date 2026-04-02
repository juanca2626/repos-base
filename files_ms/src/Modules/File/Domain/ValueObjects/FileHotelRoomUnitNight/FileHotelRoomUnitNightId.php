<?php

namespace Src\Modules\File\Domain\ValueObjects\FileHotelRoomUnitNight;

use Src\Shared\Domain\ValueObjects\IntOrNullValueObject;

final class FileHotelRoomUnitNightId extends IntOrNullValueObject
{
    public function __construct(int|null  $id)
    {
        parent::__construct($id);
    }
}
