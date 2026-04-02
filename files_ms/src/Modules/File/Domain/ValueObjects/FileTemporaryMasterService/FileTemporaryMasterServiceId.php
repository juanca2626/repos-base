<?php

namespace Src\Modules\File\Domain\ValueObjects\FileTemporaryMasterService;

use Src\Shared\Domain\ValueObjects\IntOrNullValueObject;

final class FileTemporaryMasterServiceId extends IntOrNullValueObject
{
    public function __construct(int|null $file_itinerary_id)
    {
        parent::__construct($file_itinerary_id);
    }
}
