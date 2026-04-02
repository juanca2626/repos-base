<?php

namespace Src\Modules\File\Domain\ValueObjects\FileTemporaryService;

use Src\Shared\Domain\ValueObjects\FloatValueObject;
use Src\Shared\Domain\ValueObjects\StringOrNullableValueObject;
use Src\Shared\Domain\ValueObjects\StringValueObject;

final class ExecutiveCode extends StringOrNullableValueObject
{
    public function __construct(string|null $executiveCode)
    {
        parent::__construct($executiveCode);
    }
}
