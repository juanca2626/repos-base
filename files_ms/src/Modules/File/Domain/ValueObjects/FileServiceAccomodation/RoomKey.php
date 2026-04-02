<?php

namespace Src\Modules\File\Domain\ValueObjects\FileServiceAccomodation;

use Src\Shared\Domain\ValueObjects\StringOrNullableValueObject;

final class RoomKey extends StringOrNullableValueObject
{
    public function __construct(string|null $roomKey)
    {
        parent::__construct($roomKey);
    }
}
