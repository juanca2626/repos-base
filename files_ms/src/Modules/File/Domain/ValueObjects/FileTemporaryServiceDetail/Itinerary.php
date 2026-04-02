<?php

namespace Src\Modules\File\Domain\ValueObjects\FileTemporaryServiceDetail;

use Src\Shared\Domain\ValueObjects\StringValueObject;

final class Itinerary extends StringValueObject
{
    public function __construct(string $itinerary)
    {
        parent::__construct($itinerary);
    }
}
