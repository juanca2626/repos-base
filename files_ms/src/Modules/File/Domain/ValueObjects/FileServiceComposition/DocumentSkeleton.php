<?php

namespace Src\Modules\File\Domain\ValueObjects\FileServiceComposition;

use Src\Shared\Domain\ValueObjects\BooleanValueObject;
use Src\Shared\Domain\ValueObjects\FloatValueObject;

final class DocumentSkeleton extends BooleanValueObject
{
    public function __construct(bool $documentSkeleton)
    {
        parent::__construct($documentSkeleton);
    }
}
