<?php

namespace Src\Modules\File\Domain\ValueObjects\File;

use Src\Shared\Domain\ValueObjects\IntOrNullValueObject;

final class TypeClassId extends IntOrNullValueObject
{
    public function __construct(int|null $typeClassId)
    {
        parent::__construct($typeClassId);
    }
}
