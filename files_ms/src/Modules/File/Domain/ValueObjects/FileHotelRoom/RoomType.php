<?php

namespace Src\Modules\File\Domain\ValueObjects\FileHotelRoom;

use Src\Shared\Domain\ValueObjects\FloatValueObject;
use Src\Shared\Domain\ValueObjects\StringOrNullableValueObject;
use Src\Shared\Domain\ValueObjects\StringValueObject;

final class RoomType extends StringValueObject
{
    public function __construct(string $ratePlanId)
    {
        parent::__construct($ratePlanId);
    }
}
