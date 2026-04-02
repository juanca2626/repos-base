<?php

namespace Src\Shared\Domain\Exceptions;

use Exception;

final class InvalidDateException extends \Exception
{
    public function __construct($message = "Invalid Date", $code = 400, Exception $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
