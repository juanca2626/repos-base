<?php

namespace Src\Modules\File\Domain\Exceptions;

use DomainException;


class FileItineraryCancelationException extends DomainException
{
    public function __construct(string $msn)
    {
        parent::__construct($msn, 404);
    }
}
