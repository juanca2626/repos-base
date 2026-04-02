<?php

namespace Src\Modules\File\Domain\ValueObjects\File;

use Src\Shared\Domain\ValueObjects\StringOrNullableValueObject;

final class ReservationNumber extends StringOrNullableValueObject
{
    public function __construct(string|null $reservationNumber)
    {
        parent::__construct($reservationNumber);
    }
}
