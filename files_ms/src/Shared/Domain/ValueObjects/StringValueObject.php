<?php

namespace Src\Shared\Domain\ValueObjects;

use Src\Shared\Domain\Exceptions\EmptyArgumentException;
use Src\Shared\Domain\ValueObject;

class StringValueObject extends ValueObject
{
    private string $value;

    public function __construct(string $value)
    {
        $this->value = $value;
    }

    /**
     * @throws EmptyArgumentException
     */
    protected function notEmpty(string $value): void
    {
        if (empty($value)) {
            throw new EmptyArgumentException(); // $this->exceptionMessage, $this->exceptionCode
        }
    }

    public function value(): string
    {
        return $this->value;
    }

    /**
     * @return mixed
     */
    public function jsonSerialize(): string
    {
        return $this->value;
    }
}
