<?php

namespace Src\Modules\File\Domain\ValueObjects\FileHotelRoomUnit;

use Src\Shared\Domain\ValueObjects\IntValueObject;

final class InfantNum extends IntValueObject
{
    public function __construct(int $infantNum)
    {
        parent::__construct($infantNum);
    }
}
