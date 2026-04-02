<?php

namespace Src\Modules\File\Domain\ValueObjects\FileTemporaryServiceComposition;

use Src\Shared\Domain\ValueObjects\BooleanOrNullValueObject;

final class VoucherNumber extends BooleanOrNullValueObject
{
    public function __construct(bool|null $voucherNumber)
    {
        parent::__construct($voucherNumber);
    }
}
