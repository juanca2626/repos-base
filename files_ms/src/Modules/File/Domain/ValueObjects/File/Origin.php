<?php

namespace Src\Modules\File\Domain\ValueObjects\File;

use Src\Shared\Domain\ValueObjects\EnumValueObject;

final class Origin extends EnumValueObject
{
    public function __construct(string $origin)
    {
        parent::__construct($origin, ['aurora', 'stela']);
    }
}
