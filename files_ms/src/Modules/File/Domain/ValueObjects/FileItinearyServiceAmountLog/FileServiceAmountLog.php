<?php

namespace Src\Modules\File\Domain\ValueObjects\FileItinearyServiceAmountLog;


use Src\Shared\Domain\ValueObject;

final class FileServiceAmountLog extends ValueObject
{
    public readonly object $fileServiceAmountLog;

    public function __construct(object $fileServiceAmountLog)
    {
        $this->fileServiceAmountLog = $fileServiceAmountLog;
    }

    public function getfileAmountReason(): FileServiceAmountLog
    {
        return new FileServiceAmountLog($this->fileServiceAmountLog);
    }

    public function toArray(): object
    {
        return $this->fileServiceAmountLog;
    }

    /**
     * @return object
     */
    public function jsonSerialize(): object
    {
        return $this->fileServiceAmountLog;
    }
}
