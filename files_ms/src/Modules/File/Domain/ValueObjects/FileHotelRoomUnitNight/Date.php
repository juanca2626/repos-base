<?php

namespace Src\Modules\File\Domain\ValueObjects\FileHotelRoomUnitNight;

use Src\Shared\Domain\ValueObjects\DateValueObject;

final class Date extends DateValueObject
{
    public function __construct(string $date)
    {
        parent::__construct($date);
    }
}
