<?php

namespace Src\Modules\File\Domain\ValueObjects\FileTemporaryMasterService;

use Src\Shared\Domain\ValueObjects\IntValueObject;

final class MasterServiceId extends IntValueObject
{
    public function __construct(int $master_service_id)
    {
        parent::__construct($master_service_id);
    }
}
