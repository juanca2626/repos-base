<?php

namespace Src\Modules\File\Domain\ValueObjects\FileTemporaryServiceComposition;

use Src\Shared\Domain\ValueObjects\IntOrNullValueObject;

final class FileTemporaryServiceCompositionId extends IntOrNullValueObject
{
    public function __construct(int|null $fileServiceCompositionId)
    {
        parent::__construct($fileServiceCompositionId);
    }
}
