<?php

namespace Src\Modules\File\Domain\ValueObjects\FileTemporaryMasterService;

use Src\Shared\Domain\ValueObjects\StringOrNullableValueObject;

final class StartTime extends StringOrNullableValueObject
{
    public function __construct(string|null $start_time)
    {
        parent::__construct($start_time);
    }
}
