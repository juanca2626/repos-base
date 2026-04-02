<?php

namespace Src\Modules\File\Domain\ValueObjects\FileHotelRoom;

use Src\Shared\Domain\ValueObjects\StringOrNullableValueObject;

final class ChannelReservationCodeMaster extends StringOrNullableValueObject
{
    public function __construct(string|null $channelReservationCodeMaster)
    {
        parent::__construct($channelReservationCodeMaster);
    }
}