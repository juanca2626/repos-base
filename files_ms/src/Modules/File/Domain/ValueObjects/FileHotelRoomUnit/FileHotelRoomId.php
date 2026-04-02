<?php

namespace Src\Modules\File\Domain\ValueObjects\FileHotelRoomUnit;

use Src\Shared\Domain\ValueObjects\IntOrNullValueObject;

final class FileHotelRoomId extends IntOrNullValueObject
{
    public function __construct(int|null $fileHotelRoomId)
    {
        parent::__construct($fileHotelRoomId);
    }
}
