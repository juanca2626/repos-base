<?php

namespace Src\Modules\File\Domain\ValueObjects\FileServiceCompositionSupplier;

use Src\Shared\Domain\ValueObjects\BooleanValueObject;

final class ReservationForSend extends BooleanValueObject
{
    public function __construct(bool $reservationForSend)
    {
        parent::__construct($reservationForSend);
    }
}
