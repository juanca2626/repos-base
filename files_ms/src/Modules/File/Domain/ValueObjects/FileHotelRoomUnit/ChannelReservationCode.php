<?php

namespace Src\Modules\File\Domain\ValueObjects\FileHotelRoomUnit;

use Src\Shared\Domain\ValueObjects\FloatValueObject;
use Src\Shared\Domain\ValueObjects\StringOrNullableValueObject;
use Src\Shared\Domain\ValueObjects\StringValueObject;

final class ChannelReservationCode extends StringOrNullableValueObject
{
    public function __construct(String|null $executiveCode)
    {
        parent::__construct($executiveCode);
    }
}
