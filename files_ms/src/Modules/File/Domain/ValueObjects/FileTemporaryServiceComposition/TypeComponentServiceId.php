<?php

namespace Src\Modules\File\Domain\ValueObjects\FileTemporaryServiceComposition;

use Src\Shared\Domain\ValueObjects\IntOrNullValueObject;

final class TypeComponentServiceId extends IntOrNullValueObject
{
    public function __construct(int|null $type_component_service_id)
    {
        parent::__construct($type_component_service_id);
    }
}
