<?php

namespace Src\Modules\File\Domain\ValueObjects\FileServiceComposition;

use Src\Shared\Domain\ValueObjects\IntOrNullValueObject;
use Src\Shared\Domain\ValueObjects\IntValueObject;

final class CompositionId extends IntOrNullValueObject
{
    public function __construct(int|null $compositionId)
    {
        parent::__construct($compositionId);
    }
}
