<?php

namespace Src\Shared\Domain\ValueObjects;

use Src\Shared\Domain\Exceptions\EmptyArgumentException;
use Src\Shared\Domain\ValueObject;

class IntValueObject extends ValueObject
{
    private int $value;

    public function __construct(int $value)
    {
        $this->value = $value;
    }

    public function value(): int
    {
        return $this->value;
    }

    public function setValue(int $value): int
    {
        return $this->value = $value;
    }

    public function __toString(): string
    {
        return $this->value;
    }
    /**
     * @return mixed
     */
    public function jsonSerialize(): int
    {
       return $this->value;
    }
}
