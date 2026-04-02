<?php

namespace Src\Modules\File\Domain\ValueObjects\FileServiceComposition;

use Src\Shared\Domain\ValueObjects\FloatValueObject;

final class MarkupCreated extends FloatValueObject
{
    public function __construct(float $markupCreated)
    {
        parent::__construct($markupCreated);
    }
}
