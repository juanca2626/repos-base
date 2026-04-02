<?php

namespace Src\Modules\File\Domain\ValueObjects\FileServiceCompositionSupplier;

use Src\Shared\Domain\ValueObjects\BooleanValueObject;

final class Assigned extends BooleanValueObject
{
    public function __construct(bool $assigned)
    {
        parent::__construct($assigned);
    }
}
