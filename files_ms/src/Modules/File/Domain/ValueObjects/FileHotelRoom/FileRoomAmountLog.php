<?php

namespace Src\Modules\File\Domain\ValueObjects\FileHotelRoom;


use Src\Shared\Domain\ValueObject;

final class FileRoomAmountLog extends ValueObject
{
    public readonly object $fileRoomAmountLog;

    public function __construct(object $fileRoomAmountLog)
    { 
        $this->fileRoomAmountLog = $fileRoomAmountLog;
    }

    public function getFileRoomAmountLog(): FileRoomAmountLog
    {
        return new FileRoomAmountLog($this->fileRoomAmountLog);
    }

    public function toArray(): object
    {
        return $this->fileRoomAmountLog;
    }

    /**
     * @return object
     */
    public function jsonSerialize(): object
    {
        return $this->fileRoomAmountLog;
    }
}
