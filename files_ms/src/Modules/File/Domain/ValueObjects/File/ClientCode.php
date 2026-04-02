<?php

namespace Src\Modules\File\Domain\ValueObjects\File;

use Src\Shared\Domain\ValueObjects\StringOrNullableValueObject;

final class ClientCode extends StringOrNullableValueObject
{
    public function __construct(string|null $clienCode)
    {
        parent::__construct($clienCode);
    }
}
