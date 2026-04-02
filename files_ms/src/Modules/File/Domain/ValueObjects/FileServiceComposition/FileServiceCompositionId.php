<?php

namespace Src\Modules\File\Domain\ValueObjects\FileServiceComposition;

use Src\Shared\Domain\ValueObjects\IntOrNullValueObject;

final class FileServiceCompositionId extends IntOrNullValueObject
{
    public function __construct(int|null $fileServiceCompositionId)
    {
        parent::__construct($fileServiceCompositionId);
    }
}
