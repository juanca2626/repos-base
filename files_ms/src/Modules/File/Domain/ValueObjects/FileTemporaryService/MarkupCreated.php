<?php

namespace Src\Modules\File\Domain\ValueObjects\FileTemporaryService;

use Src\Modules\File\Domain\Exceptions\InvalidDecimalValueException;
use Src\Shared\Domain\ValueObjects\FloatValueObject;

final class MarkupCreated extends FloatValueObject
{
    public function __construct(float $markupCreated)
    {
        parent::__construct($markupCreated);
    }
}
