<?php

namespace Src\Shared\Domain\ValueObjects;

use Src\Shared\Domain\Exceptions\EmptyArgumentException;
use Src\Shared\Domain\ValueObject;

class IntOrNullValueObject extends ValueObject
{
    private int|null $value;

    public function __construct(int|null $value)
    {
        $this->value = $value;
    }

    public function value(): int|null
    {
        return $this->value;
    }

    public function setValue(int $value): int
    {
        return $this->value = $value;
    }

    /**
     * @return int|null
     */
    public function jsonSerialize(): int|null
    {
        return $this->value;
    }
}
