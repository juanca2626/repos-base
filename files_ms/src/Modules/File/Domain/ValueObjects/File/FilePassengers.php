<?php

namespace Src\Modules\File\Domain\ValueObjects\File;

use Src\Modules\File\Domain\Model\FilePassenger;
use Src\Shared\Domain\ValueObjects\ValueObjectArray;

final class FilePassengers extends ValueObjectArray
{
    public readonly array $filePassengers;

    public function __construct(array $filePassengers)
    {
        parent::__construct($filePassengers);

        $this->filePassengers = array_values($filePassengers);
    }

    public function add(FilePassenger $filePassengers): void
    {
        $this->append($filePassengers);
    }

    public function getPassengers(): FilePassengers
    {
        return new FilePassengers($this->filePassengers);
    }

    public function value(): array
    {
        return $this->filePassengers;
    }

    public function toArray(): array
    {
        return $this->filePassengers;
    }

    /**
     * @return array
     */
    public function jsonSerialize(): array
    {
        return $this->filePassengers;
    }
}
