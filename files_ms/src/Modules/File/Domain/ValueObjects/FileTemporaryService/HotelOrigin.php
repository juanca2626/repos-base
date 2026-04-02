<?php

namespace Src\Modules\File\Domain\ValueObjects\FileTemporaryService;

use Src\Shared\Domain\ValueObjects\BooleanValueObject;

final class HotelOrigin extends BooleanValueObject
{
    public function __construct(bool|null $hotelOrigin)
    {
        parent::__construct($hotelOrigin);
    }
}
