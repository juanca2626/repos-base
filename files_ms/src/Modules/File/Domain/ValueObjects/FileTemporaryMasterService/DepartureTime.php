<?php

namespace Src\Modules\File\Domain\ValueObjects\FileTemporaryMasterService;

use Src\Shared\Domain\ValueObjects\StringOrNullableValueObject;

final class DepartureTime extends StringOrNullableValueObject
{
    public function __construct(string|null $departure_time)
    {
        parent::__construct($departure_time);
    }
}
