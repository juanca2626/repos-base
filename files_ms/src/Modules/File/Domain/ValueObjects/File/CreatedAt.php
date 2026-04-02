<?php

namespace Src\Modules\File\Domain\ValueObjects\File;

use Src\Shared\Domain\ValueObjects\DateValueObject;

final class CreatedAt extends DateValueObject
{
    public function __construct(string|null $dateIn)
    {
        parent::__construct($dateIn);
    }
}
