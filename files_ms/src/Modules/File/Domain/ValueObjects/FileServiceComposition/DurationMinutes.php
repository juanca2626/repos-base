<?php

namespace Src\Modules\File\Domain\ValueObjects\FileServiceComposition;

use Src\Shared\Domain\ValueObjects\IntOrNullValueObject;
use Src\Shared\Domain\ValueObjects\IntValueObject;

final class DurationMinutes extends IntOrNullValueObject
{
    public function __construct(int|null $durationMinutes)
    {
        parent::__construct($durationMinutes);
    }
}
