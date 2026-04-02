<?php

namespace Src\Modules\File\Domain\ValueObjects\FileHotelRoomUnit;

use Src\Shared\Domain\ValueObjects\IntValueObject;

final class ExtraNum extends IntValueObject
{
    public function __construct(int $extraNum)
    {
        parent::__construct($extraNum);
    }
}
