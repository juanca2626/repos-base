<?php

namespace Src\Modules\File\Domain\ValueObjects\File;

use Src\Shared\Domain\ValueObjects\IntOrNullValueObject;

final class OrderNumber extends IntOrNullValueObject
{
    public function __construct(int|null $orderNumber)
    {
        parent::__construct($orderNumber);
    }
}
