<?php

namespace Src\Modules\File\Domain\ValueObjects\FileHotelRoom;

use Src\Shared\Domain\ValueObjects\IntOrNullValueObject;

final class FileHotelRoomId extends IntOrNullValueObject
{
    public function __construct(int|null $id)
    {
        parent::__construct($id);
    }
}
