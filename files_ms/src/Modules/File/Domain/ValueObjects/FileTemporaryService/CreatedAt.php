<?php

namespace Src\Modules\File\Domain\ValueObjects\FileTemporaryService;

use Src\Shared\Domain\ValueObjects\StringOrNullableValueObject;

final class CreatedAt extends StringOrNullableValueObject
{
    public function __construct(string|null $createdAt)
    {
        parent::__construct($createdAt);
    }
}
