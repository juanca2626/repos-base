<?php

namespace Src\Modules\File\Domain\ValueObjects\File;

use Src\Shared\Domain\ValueObjects\StringOrNullableValueObject;

final class ClientName extends StringOrNullableValueObject
{
    public function __construct(string|null $clientName)
    {
        parent::__construct($clientName);
    }
}
