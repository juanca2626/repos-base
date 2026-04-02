<?php

namespace Src\Modules\File\Domain\ValueObjects\FileRoomAmountLog;


use Src\Shared\Domain\ValueObject;

final class FileAmountReason extends ValueObject
{
    public readonly object $fileAmountReason;

    public function __construct(object $fileAmountReason)
    { 
        $this->fileAmountReason = $fileAmountReason;
    }

    public function getfileAmountReason(): fileAmountReason
    {
        return new fileAmountReason($this->fileAmountReason);
    }

    public function toArray(): object
    {
        return $this->fileAmountReason;
    }

    /**
     * @return object
     */
    public function jsonSerialize(): object
    {
        return $this->fileAmountReason;
    }
}
