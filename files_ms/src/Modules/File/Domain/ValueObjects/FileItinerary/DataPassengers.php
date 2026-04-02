<?php

namespace Src\Modules\File\Domain\ValueObjects\FileItinerary;

use Src\Shared\Domain\ValueObjects\StringOrNullableValueObject;

final class DataPassengers extends StringOrNullableValueObject
{
    public function __construct(string|null $dataPassengers)
    {
        parent::__construct($dataPassengers);
    }
}
