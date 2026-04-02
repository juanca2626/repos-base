<?php

namespace Src\Shared\Exceptions;

use Exception;

abstract class DomainException extends Exception
{
    protected string $errorCode;

    public function __construct(
        string $message,
        string $errorCode,
        int $httpCode = 400
    ) {
        parent::__construct($message, $httpCode);
        $this->errorCode = $errorCode;
    }

    public function errorCode(): string
    {
        return $this->errorCode;
    }
}