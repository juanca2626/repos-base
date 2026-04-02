<?php

namespace Src\Modules\File\Domain\ValueObjects\FileTemporaryServiceComposition;

use Src\Shared\Domain\ValueObjects\BooleanValueObject;

final class IsInOpe extends BooleanValueObject
{
    public function __construct(bool $isInOpe)
    {
        parent::__construct($isInOpe);
    }
}
