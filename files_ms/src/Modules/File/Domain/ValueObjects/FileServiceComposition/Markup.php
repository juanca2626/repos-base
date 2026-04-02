<?php

namespace Src\Modules\File\Domain\ValueObjects\FileServiceComposition;

use Src\Shared\Domain\ValueObjects\FloatValueObject;

final class Markup extends FloatValueObject
{
    public function __construct(float $markup)
    {
        parent::__construct($markup);
    }
}
