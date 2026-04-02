<?php

namespace Src\Modules\File\Domain\ValueObjects\FileHotelRoomUnit;

use Src\Shared\Domain\ValueObjects\IntValueObject;

final class ChildNum extends IntValueObject
{
    public function __construct(int $childNum)
    {
        parent::__construct($childNum);
    }
}
