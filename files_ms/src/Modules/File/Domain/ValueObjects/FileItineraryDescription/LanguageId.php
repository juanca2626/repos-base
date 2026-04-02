<?php

namespace Src\Modules\File\Domain\ValueObjects\FileItineraryDescription;

use Src\Shared\Domain\ValueObjects\IntValueObject;

final class LanguageId extends IntValueObject
{
    public function __construct(int $languageId)
    {
        parent::__construct($languageId);
    }
}
