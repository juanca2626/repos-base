<?php

namespace Src\Modules\File\Domain\ValueObjects\FileHotelRoomUnit;

use Src\Shared\Domain\ValueObjects\BooleanValueObject;

final class Status extends BooleanValueObject
{
    public function __construct(bool $cancellationStatus)
    {
        parent::__construct($cancellationStatus);
    }
}
