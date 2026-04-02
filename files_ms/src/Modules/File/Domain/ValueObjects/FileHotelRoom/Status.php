<?php

namespace Src\Modules\File\Domain\ValueObjects\FileHotelRoom;

use Src\Shared\Domain\ValueObjects\BooleanValueObject;
use Src\Shared\Domain\ValueObjects\EnumValueObject;
use Src\Shared\Domain\ValueObjects\IntValueObject;

final class Status extends BooleanValueObject
{
    public function __construct(bool $status)
    {
        parent::__construct($status);
    }
}
