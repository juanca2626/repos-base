<?php

namespace Src\Modules\File\Domain\Exceptions;

use DomainException;


class InvalidEntityException extends DomainException
{
    public function __construct(private readonly string $value)
    {
        parent::__construct();
    }

    public function errorCode(): string
    {
        return 'entity_invalid';
    }

    public function errorMessage(): string
    {
        return sprintf('EntityObject <%d> invalid', $this->value);
    }
}
