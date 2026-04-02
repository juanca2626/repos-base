<?php

namespace Src\Modules\File\Domain\ValueObjects\FileTemporaryServiceCompositionSupplier;

use Src\Shared\Domain\ValueObjects\BooleanValueObject;

final class ForAssign extends BooleanValueObject
{
    public function __construct(bool $forAssign)
    {
        parent::__construct($forAssign);
    }
}
