<?php

namespace Src\Modules\File\Domain\ValueObjects\FileRoomAccomodation;

use Src\Shared\Domain\ValueObjects\StringOrNullableValueObject;

final class RoomKey extends StringOrNullableValueObject
{
    public function __construct(string $roomKey)
    {
        parent::__construct($roomKey);
    }
}
