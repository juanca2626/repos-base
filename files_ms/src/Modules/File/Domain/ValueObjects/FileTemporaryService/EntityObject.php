<?php

namespace Src\Modules\File\Domain\ValueObjects\FileTemporaryService;

use Src\Modules\File\Domain\Exceptions\InvalidEntityException;
use Src\Shared\Domain\ValueObjects\EnumValueObject;
use Src\Shared\Domain\ValueObjects\StringOrNullableValueObject;

final class EntityObject extends EnumValueObject
{
    public function __construct(string $entity)
    {
        parent::__construct($entity, ['service', 'service-mask', 'service-temporary', 'hotel', 'flight']);
    }
}
