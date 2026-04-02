<?php

namespace Src\Modules\File\Domain\ValueObjects\FileServiceComposition;

use Src\Shared\Domain\ValueObjects\IntOrNullValueObject;

final class TypeCompositionId extends IntOrNullValueObject
{
    public function __construct(int|null $type_composition_id)
    {
        parent::__construct($type_composition_id);
    }
}
