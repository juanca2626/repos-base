<?php

namespace Src\Modules\File\Domain\ValueObjects\FileServiceUnit;

use Src\Shared\Domain\ValueObjects\ValueObjectArray;

final class Accommodation extends ValueObjectArray
{
    public readonly array $accommodations;

    public function __construct(array $accommodations)
    {
        parent::__construct($accommodations);
        $this->accommodations = $accommodations;
    }

    public function toArray(): array
    {
        return $this->accommodations;
    }

    /**
     * @return array
     */
    public function jsonSerialize(): array
    {
        return $this->accommodations;
    }
}
