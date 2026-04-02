<?php

namespace Src\Modules\File\Domain\ValueObjects\File;

use Src\Shared\Domain\ValueObjects\IntValueObject;

final class ExecutiveId extends IntValueObject
{
    public function __construct(int $executiveId)
    {
        parent::__construct($executiveId);
    }
}
