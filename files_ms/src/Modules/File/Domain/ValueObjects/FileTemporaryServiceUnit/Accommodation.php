<?php

namespace Src\Modules\File\Domain\ValueObjects\FileTemporaryServiceUnit;

use Src\Shared\Domain\ValueObjects\ValueObjectArray;

final class Accommodation extends ValueObjectArray
{
    private array $accommodations;

    public function __construct(array $accommodations)
    {
        parent::__construct($accommodations);
        $this->accommodations = $accommodations;
    }

    public function toArray(): array
    {
        return $this->accommodations;
    }

    public function setValue(array $accommodation): array
    {
        return $this->accommodations = $accommodation;
    }


    /**
     * @return array
     */
    public function jsonSerialize(): array
    {
        return $this->accommodations;
    }
}
