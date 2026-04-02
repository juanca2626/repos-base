<?php

namespace Src\Modules\File\Domain\ValueObjects\FilePassenger;

use Src\Shared\Domain\ValueObjects\StringOrNullableValueObject;

final class MedicalRestrictions extends StringOrNullableValueObject
{
    public function __construct(string|null $medicalRestrictions)
    {
        parent::__construct($medicalRestrictions);
    }
}
