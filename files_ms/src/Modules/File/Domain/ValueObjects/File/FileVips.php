<?php

namespace Src\Modules\File\Domain\ValueObjects\File;


use Src\Shared\Domain\ValueObjects\ValueObjectArray;

final class FileVips extends ValueObjectArray
{
    public readonly array $fileVips;

    public function __construct(array $fileVips)
    {
        parent::__construct($fileVips);

        $this->fileVips = array_values($fileVips);
    }

    public function toArray(): array
    {
        return $this->fileVips;
    }

    public function getFileVips(): FileVips
    {
        return new FileVips($this->fileVips);
    }

    /**
     * @return array
     */
    public function jsonSerialize(): array
    {
        return $this->fileVips;
    }
}
