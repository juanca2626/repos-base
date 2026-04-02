<?php

namespace Src\Modules\File\Domain\ValueObjects\Vip;

use Src\Shared\Domain\ValueObjects\IntValueObject;

class VipId extends IntValueObject
{
    public function __construct(int $vipId)
    {
        parent::__construct($vipId);
    }
}
