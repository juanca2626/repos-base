<?php

namespace Src\Modules\File\Domain\Exceptions;

use DomainException;


class InvalidDecimalValueException extends DomainException
{
    public function __construct(private readonly float $value)
    {
        parent::__construct();
    }

    public function errorCode(): string
    {
        return 'decimal_invalid';
    }

    public function errorMessage(): string
    {
        return sprintf('Decimal <%d> invalid', $this->value);
    }
}
