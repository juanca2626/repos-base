<?php

namespace Src\Modules\File\Domain\ValueObjects\Vip;

use Src\Shared\Domain\ValueObjects\EnumValueObject;
use Src\Shared\Domain\ValueObjects\IntValueObject;

class EntityVip extends EnumValueObject
{
    public function __construct(string $entityVip)
    {
        parent::__construct($entityVip, ['file', 'client']);
    }
}
