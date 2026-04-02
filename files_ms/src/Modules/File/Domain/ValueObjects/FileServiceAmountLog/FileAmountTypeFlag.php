<?php

namespace Src\Modules\File\Domain\ValueObjects\FileServiceAmountLog;


use Src\Shared\Domain\ValueObject;

final class FileAmountTypeFlag extends ValueObject
{
    public readonly object $fileAmountTypeFlag;

    public function __construct(object $fileAmountTypeFlag)
    {
        $this->fileAmountTypeFlag = $fileAmountTypeFlag;
    }

    public function toArray(): object
    {
        return $this->fileAmountTypeFlag;
    }

    /**
     * @return object
     */
    public function jsonSerialize(): object
    {
        return $this->fileAmountTypeFlag;
    }
}
