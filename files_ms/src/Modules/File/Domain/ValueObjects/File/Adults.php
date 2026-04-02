<?php

namespace Src\Modules\File\Domain\ValueObjects\File;

use Src\Shared\Domain\ValueObjects\IntValueObject;

final class Adults extends IntValueObject
{
    public function __construct(int $adults)
    {
        parent::__construct($adults);
    }
}
