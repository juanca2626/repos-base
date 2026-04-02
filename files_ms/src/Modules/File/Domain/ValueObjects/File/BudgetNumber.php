<?php

namespace Src\Modules\File\Domain\ValueObjects\File;

use Src\Shared\Domain\ValueObjects\StringOrNullableValueObject;

final class BudgetNumber extends StringOrNullableValueObject
{

    public function __construct(string|null $budgetNumber)
    {
        parent::__construct($budgetNumber);
    }
}
