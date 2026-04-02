<?php

namespace Src\Modules\File\Domain\ValueObjects\FileHotelRoomUnit;

use Src\Shared\Domain\ValueObjects\IntOrNullValueObject;

final class FileAmountTypeFlagId extends IntOrNullValueObject
{
    public function __construct(int|null $id)
    {
        parent::__construct($id);
    }
}
