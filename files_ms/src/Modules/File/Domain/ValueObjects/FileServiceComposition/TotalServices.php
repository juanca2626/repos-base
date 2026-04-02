<?php

namespace Src\Modules\File\Domain\ValueObjects\FileServiceComposition;

use Src\Shared\Domain\ValueObjects\IntValueObject;

final class TotalServices extends IntValueObject
{
    public function __construct(int $totalServices)
    {
        parent::__construct($totalServices);
    }
}
