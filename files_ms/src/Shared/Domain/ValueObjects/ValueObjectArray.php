<?php

namespace Src\Shared\Domain\ValueObjects;

use Src\Shared\Domain\Exceptions\EmptyArgumentException;

abstract class ValueObjectArray extends \ArrayIterator implements \JsonSerializable
{
    abstract public function jsonSerialize(): array;
}
