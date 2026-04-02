<?php

namespace Src\Modules\File\Domain\ValueObjects\File;

use Src\Shared\Domain\ValueObjects\ValueObjectArray;

final class FileCategories extends ValueObjectArray
{
    public readonly array $fileCategories;

    public function __construct(array $fileCategories)
    {
        parent::__construct($fileCategories);
        $this->fileCategories = array_values($fileCategories);
    }

    public function getCategories(): FileCategories
    {
        return new FileCategories($this->fileCategories);
    }

    public function toArray(): array
    {
        return $this->fileCategories;
    }

    /**
     * @return array
     */
    public function jsonSerialize(): array
    {
        return $this->fileCategories;
    }
}
