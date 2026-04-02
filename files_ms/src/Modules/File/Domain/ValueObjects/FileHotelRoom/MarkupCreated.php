<?php

namespace Src\Modules\File\Domain\ValueObjects\FileHotelRoom;

use Src\Shared\Domain\ValueObjects\FloatValueObject;

final class MarkupCreated extends FloatValueObject
{
    public function __construct(float $markupCreated = 0)
    {
        parent::__construct($markupCreated);
    }
}
