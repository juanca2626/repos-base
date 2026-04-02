<?php

namespace Src\Modules\File\Domain\ValueObjects\File;

use Src\Shared\Domain\ValueObjects\ValueObjectArray;

final class FileStatusReasons extends ValueObjectArray
{
    public readonly array $fileStatusReasons;

    public function __construct(array $fileStatusReasons)
    {
        parent::__construct($fileStatusReasons);
        $this->fileStatusReasons = array_values($fileStatusReasons);
    }

    public function getFileStatusReasons(): FileStatusReasons
    {
        return new FileStatusReasons($this->fileStatusReasons);
    }

    public function toArray(): array
    {
        return $this->fileStatusReasons;
    }

    /**
     * @return array
     */
    public function jsonSerialize(): array
    {
        return $this->fileStatusReasons;
    }
}
