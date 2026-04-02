<?php

namespace Src\Shared\Domain\Exceptions;

use Exception;

final class InvalidTypeException extends \Exception
{
    public function __construct($message = "Invalid type", $code = 400, Exception $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
