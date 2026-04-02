<?php

namespace Src\Modules\File\Domain\ValueObjects\FileHotelRoom;

use Src\Shared\Domain\ValueObjects\BooleanValueObject;

final class UseVoucher extends BooleanValueObject
{
    public function __construct(bool $useVoucher)
    {
        parent::__construct($useVoucher);
    }
}
