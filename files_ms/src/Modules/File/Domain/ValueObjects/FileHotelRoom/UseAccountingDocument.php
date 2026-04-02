<?php

namespace Src\Modules\File\Domain\ValueObjects\FileHotelRoom;

use Src\Shared\Domain\ValueObjects\BooleanOrNullValueObject;

final class UseAccountingDocument extends BooleanOrNullValueObject
{
    public function __construct(bool|null $useAccountingDocument)
    {
        parent::__construct($useAccountingDocument);
    }
}
