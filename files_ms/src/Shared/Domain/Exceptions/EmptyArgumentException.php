<?php

namespace Src\Shared\Domain\Exceptions;

use Exception;

final class EmptyArgumentException extends \Exception
{
    public function __construct($message = "Invalid argument", $code = 400, Exception $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
