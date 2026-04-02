<?php

namespace Src\Modules\File\Domain\ValueObjects\FileHotelRoomUnit;

use Src\Shared\Domain\ValueObjects\IntOrNullValueObject;

final class ReservationsRatesPlansRoomsId extends IntOrNullValueObject
{
    public function __construct(int $reservationsRatesPlansRoomsId)
    {
        parent::__construct($reservationsRatesPlansRoomsId);
    }
}
