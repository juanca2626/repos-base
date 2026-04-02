<?php

namespace Src\Modules\File\Domain\ValueObjects\FileTemporaryServiceComposition;

use Src\Shared\Domain\ValueObjects\BooleanValueObject;

final class IsProgrammable extends BooleanValueObject
{
    public function __construct(bool $isProgrammable)
    {
        parent::__construct($isProgrammable);
    }
}
