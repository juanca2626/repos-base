<?php

namespace Src\Modules\File\Domain\ValueObjects\FileHotelRoom;

use Src\Shared\Domain\ValueObjects\BooleanValueObject;

final class UseItinerary extends BooleanValueObject
{
    public function __construct(bool $useItinerary)
    {
        parent::__construct($useItinerary);
    }
}
