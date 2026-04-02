<?php

namespace Src\Modules\File\Domain\ValueObjects\FileHotelRoom;

use Src\Shared\Domain\ValueObjects\IntValueObject;

final class ItemNumber extends IntValueObject
{
    public function __construct(int $itemNumber)
    {
        parent::__construct($itemNumber);
    }
}
