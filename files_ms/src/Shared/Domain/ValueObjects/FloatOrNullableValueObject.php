<?php

namespace Src\Shared\Domain\ValueObjects;

use Src\Shared\Domain\Exceptions\EmptyArgumentException;
use Src\Shared\Domain\ValueObject;

class FloatOrNullableValueObject extends ValueObject
{
    private float|null $value;

    public function __construct(float|null $value)
    {
        $this->value = $value;
    }

    public function value(): float|null
    {
        return $this->value;
    }

    /**
     * @return mixed
     */
    public function jsonSerialize(): float|null
    {
        return  $this->value;
    }
}
