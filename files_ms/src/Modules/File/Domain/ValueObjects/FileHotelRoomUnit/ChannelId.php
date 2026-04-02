<?php

namespace Src\Modules\File\Domain\ValueObjects\FileHotelRoomUnit;

use Src\Shared\Domain\ValueObjects\IntValueObject;

final class ChannelId extends IntValueObject
{
    public function __construct(int $channelId)
    {
        parent::__construct($channelId);
    }
}
