<?php

namespace Src\Shared\Domain\ValueObjects;

use Src\Shared\Domain\ValueObject;

class BooleanOrNullValueObject extends ValueObject
{
    private bool|null $value;

    public function __construct(bool|null $value)
    {
        $this->value = $value;
    }

    public function value(): bool|null
    {
        return $this->value;
    }

    /**
     * @return bool|null
     */
    public function jsonSerialize(): bool|null
    {
        return $this->value;
    }
}
