<?php

namespace Src\Modules\File\Domain\ValueObjects\FileHotelRoomUnit;

use Src\Shared\Domain\ValueObjects\IntOrNullValueObject;

final class RatesPlansRoomsId extends IntOrNullValueObject
{
    public function __construct(int $ratesPlansRoomsId)
    {
        parent::__construct($ratesPlansRoomsId);
    }
}
