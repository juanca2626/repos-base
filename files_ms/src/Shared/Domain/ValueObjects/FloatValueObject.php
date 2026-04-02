<?php

namespace Src\Shared\Domain\ValueObjects;

use Src\Shared\Domain\Exceptions\EmptyArgumentException;
use Src\Shared\Domain\ValueObject;

class FloatValueObject extends ValueObject
{
    private float|null $value;

    public function __construct(float|null $value)
    {
        $this->value = $value;
    }

    public function value(): float
    {
        return $this->value;
    }

    public function setValue(float $value): float
    {
        return $this->value = $value;
    }

    /**
     * @return mixed
     */
    public function jsonSerialize(): float
    {
        return  $this->value;
    }
}
