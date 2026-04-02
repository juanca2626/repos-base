<?php

namespace Src\Modules\File\Domain\ValueObjects\FileHotelRoom;

use Src\Shared\Domain\ValueObjects\IntValueObject;

final class TotalInfants extends IntValueObject
{
    public function __construct(int $totalInfants)
    {
        parent::__construct($totalInfants);
    }
}
