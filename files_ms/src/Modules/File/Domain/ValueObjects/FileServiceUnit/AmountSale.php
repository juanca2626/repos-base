<?php

namespace Src\Modules\File\Domain\ValueObjects\FileServiceUnit;

use Src\Shared\Domain\ValueObjects\FloatValueObject;

final class AmountSale extends FloatValueObject
{
    public function __construct(float $amountSale)
    {
        parent::__construct($amountSale);
    }
}
