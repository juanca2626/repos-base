<?php

namespace Src\Modules\File\Domain\ValueObjects\FileHotelRoom;

use Src\Shared\Domain\ValueObjects\IntValueObject;

final class TotalExtra extends IntValueObject
{
    public function __construct(int $totalExtra)
    {
        parent::__construct($totalExtra);
    }
}
