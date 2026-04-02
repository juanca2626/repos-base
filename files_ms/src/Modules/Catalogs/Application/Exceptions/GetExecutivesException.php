<?php

namespace Src\Modules\Catalogs\Application\Exceptions;

use RuntimeException;

class GetExecutivesException extends RuntimeException
{
    public function __construct(
        string $message,
        private string $errorCode,
        ?\Throwable $previous = null
    ) {
        parent::__construct($message, 0, $previous);
    }

    public function errorCode(): string
    {
        return $this->errorCode;
    }
}