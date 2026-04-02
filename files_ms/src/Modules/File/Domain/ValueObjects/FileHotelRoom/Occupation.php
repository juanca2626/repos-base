<?php

namespace Src\Modules\File\Domain\ValueObjects\FileHotelRoom;

use Src\Shared\Domain\ValueObjects\IntValueObject;

final class Occupation extends IntValueObject
{
    public function __construct(int $occupation)
    {
        parent::__construct($occupation);
    }
}
