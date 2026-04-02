<?php

namespace Src\Modules\File\Domain\ValueObjects\FileHotelRoom;

use Src\Shared\Domain\ValueObjects\IntValueObject;

final class TotalChildren extends IntValueObject
{
    public function __construct(int $totalChildren)
    {
        parent::__construct($totalChildren);
    }
}
