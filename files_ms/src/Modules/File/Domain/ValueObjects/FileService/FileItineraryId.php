<?php

namespace Src\Modules\File\Domain\ValueObjects\FileService;

use Src\Shared\Domain\ValueObjects\IntOrNullValueObject;

final class FileItineraryId extends IntOrNullValueObject
{
    public function __construct(int|null $file_itinerary_id)
    {
        parent::__construct($file_itinerary_id);
    }
}
