<?php

namespace Src\Modules\File\Domain\ValueObjects\FileHotelRoom;

use Src\Shared\Domain\ValueObjects\FloatValueObject;
use Src\Shared\Domain\ValueObjects\IntValueObject;
use Src\Shared\Domain\ValueObjects\StringOrNullableValueObject;

final class RatePlanId extends IntValueObject
{
    public function __construct(int $ratePlanId = 0)
    {
        parent::__construct($ratePlanId);
    }
}
