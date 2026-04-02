<?php

namespace Src\Modules\File\Domain\ValueObjects\FileTemporaryServiceDetail;

use Src\Shared\Domain\ValueObjects\IntOrNullValueObject;

class LanguageId extends IntOrNullValueObject
{
    public function __construct(int|null $languageId)
    {
        parent::__construct($languageId);
    }
}
