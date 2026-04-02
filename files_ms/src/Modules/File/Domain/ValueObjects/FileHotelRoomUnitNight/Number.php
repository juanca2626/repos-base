<?php

namespace Src\Modules\File\Domain\ValueObjects\FileHotelRoomUnitNight;

use Src\Shared\Domain\ValueObjects\IntOrNullValueObject;
use Src\Shared\Domain\ValueObjects\IntValueObject;

final class Number extends IntOrNullValueObject
{
    public function __construct(int|null $number)
    {
        parent::__construct($number);
    }
}
