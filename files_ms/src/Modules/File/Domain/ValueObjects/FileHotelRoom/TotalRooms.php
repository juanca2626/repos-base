<?php

namespace Src\Modules\File\Domain\ValueObjects\FileHotelRoom;

use Src\Shared\Domain\ValueObjects\IntValueObject;

final class TotalRooms extends IntValueObject
{
    public function __construct(int $totalRooms)
    {
        parent::__construct($totalRooms);
    }
}
