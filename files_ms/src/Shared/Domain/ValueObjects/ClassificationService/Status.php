<?php

namespace Src\Shared\Domain\ValueObjects\ClassificationService;

use Src\Shared\Domain\ValueObjects\StringValueObject;

final class Status extends StringValueObject
{
    public function __construct(string $status)
    {
        parent::__construct($status);
    }
}
