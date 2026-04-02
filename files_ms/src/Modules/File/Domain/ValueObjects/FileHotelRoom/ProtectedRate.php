<?php

namespace Src\Modules\File\Domain\ValueObjects\FileHotelRoom;

use Src\Shared\Domain\ValueObjects\BooleanValueObject; 

final class ProtectedRate extends BooleanValueObject
{
    public function __construct(float|null  $status)
    {
        parent::__construct($status);
    }
}
