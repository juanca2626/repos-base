<?php

namespace Src\Modules\File\Domain\ValueObjects\FileServiceComposition;

use Src\Shared\Domain\ValueObjects\DateValueObject;

final class DateOut extends DateValueObject
{
    public function __construct(string $dateOut)
    {
        parent::__construct($dateOut);
    }
}
