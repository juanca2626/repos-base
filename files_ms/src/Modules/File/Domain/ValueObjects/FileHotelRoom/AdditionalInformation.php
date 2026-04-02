<?php

namespace Src\Modules\File\Domain\ValueObjects\FileHotelRoom;

use Src\Shared\Domain\ValueObjects\StringOrNullableValueObject;

final class AdditionalInformation extends StringOrNullableValueObject
{
    public function __construct(string|null $additionalInformation)
    {
        parent::__construct($additionalInformation);
    }
}
