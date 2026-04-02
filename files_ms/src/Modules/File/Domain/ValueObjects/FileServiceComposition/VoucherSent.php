<?php

namespace Src\Modules\File\Domain\ValueObjects\FileServiceComposition;

use Src\Shared\Domain\ValueObjects\BooleanValueObject;

final class VoucherSent extends BooleanValueObject
{
    public function __construct(bool $voucherSent)
    {
        parent::__construct($voucherSent);
    }
}
