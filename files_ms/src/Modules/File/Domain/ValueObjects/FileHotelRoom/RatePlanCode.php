<?php

namespace Src\Modules\File\Domain\ValueObjects\FileHotelRoom;
use Src\Shared\Domain\ValueObjects\StringValueObject;

final class RatePlanCode extends StringValueObject
{
    public function __construct(string $ratePlanCode)
    {
        parent::__construct($ratePlanCode);
    }
}
