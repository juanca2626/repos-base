<?php

namespace Src\Modules\File\Domain\ValueObjects\File;

use Src\Shared\Domain\ValueObjects\IntOrNullValueObject;
use Src\Shared\Domain\ValueObjects\IntValueObject;

final class SerieReserveId extends IntValueObject
{
    public function __construct(int $serieReserveId)
    {
        parent::__construct($serieReserveId);
    }
}
