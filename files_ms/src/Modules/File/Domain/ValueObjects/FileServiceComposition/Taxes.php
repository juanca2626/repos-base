<?php

namespace Src\Modules\File\Domain\ValueObjects\FileServiceComposition;

use Src\Shared\Domain\ValueObjects\FloatValueObject;

final class Taxes extends FloatValueObject
{
    public function __construct(float $taxes)
    {
        parent::__construct($taxes);
    }
}
