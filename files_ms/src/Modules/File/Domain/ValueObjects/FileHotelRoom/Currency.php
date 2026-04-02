<?php

namespace Src\Modules\File\Domain\ValueObjects\FileHotelRoom;

use Src\Shared\Domain\ValueObjects\StringValueObject;

final class Currency extends StringValueObject
{
    public function __construct(string $currency = 'USD')
    {
        parent::__construct($currency);
    }
}
