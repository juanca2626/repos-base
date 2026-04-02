<?php

namespace Src\Modules\File\Domain\ValueObjects\FileItineraryFlight;

use Src\Shared\Domain\ValueObjects\IntOrNullValueObject;

final class NroPax extends IntOrNullValueObject
{
    public function __construct(int|null $nroPax)
    {
        parent::__construct($nroPax);
    }
}
