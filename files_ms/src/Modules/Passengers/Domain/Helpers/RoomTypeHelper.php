<?php

namespace Src\Modules\Passengers\Domain\Helpers;

class RoomTypeHelper
{
    public static function getDescription(?int $type): string
    {
        return match ($type) {
            1 => 'SGL',
            2 => 'DBL',
            3 => 'TPL',
            default => ''
        };
    }
}