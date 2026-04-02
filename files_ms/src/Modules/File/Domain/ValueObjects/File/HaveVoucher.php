<?php

namespace Src\Modules\File\Domain\ValueObjects\File;

use Src\Shared\Domain\ValueObjects\BooleanValueObject;

final class HaveVoucher extends BooleanValueObject
{
    public function __construct(bool $haveVoucher)
    {
        parent::__construct($haveVoucher);
    }
}
