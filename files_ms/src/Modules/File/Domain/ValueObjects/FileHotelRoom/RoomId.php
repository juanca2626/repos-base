<?php

namespace Src\Modules\File\Domain\ValueObjects\FileHotelRoom;

use Src\Shared\Domain\ValueObjects\StringOrNullableValueObject;

final class RoomId extends StringOrNullableValueObject
{
    public function __construct(string|null $roomId)
    {
        parent::__construct($roomId);
    }
}
