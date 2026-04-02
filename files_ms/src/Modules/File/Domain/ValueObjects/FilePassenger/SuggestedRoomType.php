<?php

namespace Src\Modules\File\Domain\ValueObjects\FilePassenger;

use Src\Shared\Domain\ValueObjects\StringOrNullableValueObject;

final class SuggestedRoomType extends StringOrNullableValueObject
{
    public function __construct(string|null $suggestedRoomType)
    {
        parent::__construct($suggestedRoomType);
    }
}
