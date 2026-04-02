<?php

namespace Src\Modules\File\Domain\ValueObjects\FilePassenger;

use Src\Shared\Domain\ValueObjects\StringOrNullableValueObject;

final class Email extends StringOrNullableValueObject
{
    public function __construct(string|null $email)
    {
        parent::__construct($email);
    }
}
