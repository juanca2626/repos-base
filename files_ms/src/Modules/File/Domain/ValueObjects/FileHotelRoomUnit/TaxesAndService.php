<?php

namespace Src\Modules\File\Domain\ValueObjects\FileHotelRoomUnit;

use Src\Shared\Domain\ValueObjects\FloatValueObject;
use Src\Shared\Domain\ValueObjects\StringOrNullableValueObject;
use Src\Shared\Domain\ValueObjects\StringValueObject;

final class TaxesAndService extends StringOrNullableValueObject
{
    public function __construct(string|null $taxesAndService)
    {
        parent::__construct($taxesAndService);
    }
}
