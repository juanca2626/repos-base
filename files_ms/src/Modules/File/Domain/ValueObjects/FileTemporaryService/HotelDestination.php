<?php

namespace Src\Modules\File\Domain\ValueObjects\FileTemporaryService;

use Src\Shared\Domain\ValueObjects\BooleanValueObject;

final class HotelDestination extends BooleanValueObject
{
    public function __construct(bool|null $hotelDestination)
    {
        parent::__construct($hotelDestination);
    }
}
