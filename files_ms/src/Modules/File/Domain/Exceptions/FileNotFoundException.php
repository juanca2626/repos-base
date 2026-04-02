<?php

namespace Src\Modules\File\Domain\Exceptions;

use DomainException;


class FileNotFoundException extends DomainException
{
    public function __construct(private readonly int $id)
    {
        parent::__construct();
    }

    public function errorCode(): string
    {
        return 'file_not_found';
    }

    public function errorMessage(): string
    {
        return sprintf('FileEloquentModel <%d> does not found', $this->id);
    }
}
