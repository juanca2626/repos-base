<?php

namespace Src\Modules\File\Domain\ValueObjects\FileTemporaryMasterService;

use Src\Shared\Domain\ValueObjects\FloatValueObject;

final class AmountCost extends FloatValueObject
{
    public function __construct(float $amount_cost)
    {
        parent::__construct($amount_cost);
    }
}
