<?php

namespace Src\Modules\File\Domain\ValueObjects\FileHotelRoomUnit;
 
use Src\Shared\Domain\ValueObjects\StringOrNullableValueObject; 

final class PoliciesCancellation extends StringOrNullableValueObject
{
    public function __construct(string|null $policiesCancellation)
    {
        parent::__construct($policiesCancellation);
    }
}
