<?php

namespace Src\Modules\File\Domain\ValueObjects\FileVip;

use Src\Shared\Domain\ValueObjects\ValueObjectArray;

final class Vip extends ValueObjectArray
{
    public readonly array $vip;

    public function __construct(array $vip)
    {
        parent::__construct($vip);
        $this->vip = $vip;
    }

    public function toArray(): array
    {
        return $this->vip;
    }

    /**
     * @return array
     */
    public function jsonSerialize(): array
    {
        return $this->vip;
    }
}
