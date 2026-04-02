<?php

namespace Src\Modules\File\Domain\ValueObjects\FilePassenger;

use Src\Modules\File\Domain\Exceptions\InvalidEntityException;
use Src\Shared\Domain\ValueObjects\EnumValueObject;
use Src\Shared\Domain\ValueObjects\StringOrNullableValueObject;

final class Genre extends EnumValueObject
{
    public function __construct(string|null $genre)
    {
        if (empty($genre)){
            $genre = 'M';
        }
        parent::__construct($genre, ['M', 'F']);
    }
}
