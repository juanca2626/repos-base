<?php

namespace Src\Modules\File\Domain\ValueObjects\FilePassenger;

use Src\Shared\Domain\ValueObjects\EnumValueObject;

final class TypePassenger extends EnumValueObject
{
    public function __construct(string $type = 'ADL')
    {
        parent::__construct(strtoupper($type), ['ADL', 'CHD', 'INF','TC','GUI']);
    }
}
