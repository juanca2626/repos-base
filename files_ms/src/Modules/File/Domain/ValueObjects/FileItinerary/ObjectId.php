<?php

namespace Src\Modules\File\Domain\ValueObjects\FileItinerary;

use Src\Shared\Domain\ValueObjects\IntValueObject;

class ObjectId extends IntValueObject
{
    public function __construct(int $objectId)
    {
        parent::__construct($objectId);
    }
}
