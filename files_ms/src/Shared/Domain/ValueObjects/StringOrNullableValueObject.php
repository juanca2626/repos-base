<?php

namespace Src\Shared\Domain\ValueObjects;

use Src\Shared\Domain\ValueObject;

class StringOrNullableValueObject extends ValueObject
{
    private string|null $value;

    public function __construct(string|null $value)
    {
        $this->value = $value;
    }

    public function value(): string|null
    {
        return $this->value;
    }

    public function __toString(): string
    {
        return $this->value;
    }
    /**
     * @return mixed
     */
    public function jsonSerialize(): string|null
    {
        return $this->value;
    }
}
