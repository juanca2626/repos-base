<?php

namespace Src\Modules\File\Domain\ValueObjects\FileHotelRoom;

use Src\Shared\Domain\ValueObjects\BooleanValueObject;

final class DocumentSkeleton extends BooleanValueObject
{
    public function __construct(bool $documentSkeleton)
    {
        parent::__construct($documentSkeleton);
    }
}
