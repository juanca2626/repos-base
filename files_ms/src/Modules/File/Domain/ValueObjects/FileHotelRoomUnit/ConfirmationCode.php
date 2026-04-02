<?php

namespace Src\Modules\File\Domain\ValueObjects\FileHotelRoomUnit;

use Src\Shared\Domain\ValueObjects\StringOrNullableValueObject;

final class ConfirmationCode extends StringOrNullableValueObject
{
    public function __construct(string|null $confirmationCode)
    {
        parent::__construct($confirmationCode);
    }
}
