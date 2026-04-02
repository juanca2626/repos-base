<?php

namespace Src\Modules\File\Domain\ValueObjects\FileHotelRoom;

use Src\Shared\Domain\ValueObjects\StringValueObject;

final class RoomName extends StringValueObject
{
    public function __construct(string $ratePlanId)
    {
        parent::__construct($ratePlanId);
    }
}
