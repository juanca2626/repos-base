<?php

namespace Src\Modules\File\Domain\ValueObjects\FileHotelRoomUnit;

use Src\Shared\Domain\ValueObjects\BooleanValueObject;

final class StatusHotel extends BooleanValueObject
{
    public function __construct(bool $statusHotel)
    {
        parent::__construct($statusHotel);
    }
}
