<?php

namespace Src\Shared\Domain\ValueObjects;

use Src\Shared\Domain\ValueObject;

class BooleanValueObject extends ValueObject
{
    private float|null  $value;

    public function __construct(float|null  $value)
    {
        $this->value = $value;
    }

    public function value(): bool|null
    {
        return $this->value;
    }

    /**
     * @return mixed
     */
    public function jsonSerialize(): bool|null
    {
        return $this->value;
    }
}
