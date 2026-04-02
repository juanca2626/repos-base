<?php

namespace Src\Modules\File\Domain\ValueObjects\File;

use Src\Shared\Domain\ValueObjects\StringOrNullableValueObject;

final class Applicant extends StringOrNullableValueObject
{
    public function __construct(string|null $applicant)
    {
        parent::__construct($applicant);
    }
}
