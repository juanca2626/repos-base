<?php

namespace Src\Modules\File\Domain\ValueObjects\FileHotelRoomUnit;

use Src\Shared\Domain\ValueObjects\IntValueObject;

final class AdultNum extends IntValueObject
{
    public function __construct(int $adultNum)
    {
        parent::__construct($adultNum);
    }
}
