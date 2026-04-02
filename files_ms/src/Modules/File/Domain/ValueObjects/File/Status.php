<?php

namespace Src\Modules\File\Domain\ValueObjects\File;

use Src\Shared\Domain\ValueObjects\EnumValueObject;

final class Status extends EnumValueObject
{
    public function __construct(string $status)
    {
        parent::__construct($status, ['OK', 'XL', 'BL', 'CE', 'PF']);
    }
}
