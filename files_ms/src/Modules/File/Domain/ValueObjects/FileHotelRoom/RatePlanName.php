<?php

namespace Src\Modules\File\Domain\ValueObjects\FileHotelRoom;
use Src\Shared\Domain\ValueObjects\StringValueObject;

final class RatePlanName extends StringValueObject
{
    public function __construct(string $ratePlanId)
    {
        parent::__construct($ratePlanId);
    }
}
