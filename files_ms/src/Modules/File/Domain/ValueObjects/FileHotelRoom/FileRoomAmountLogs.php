<?php

namespace Src\Modules\File\Domain\ValueObjects\FileHotelRoom;


use Src\Shared\Domain\ValueObjects\ValueObjectArray;

final class FileRoomAmountLogs extends ValueObjectArray
{
    public readonly array $fileRoomAmountLogs;

    public function __construct(array $fileRoomAmountLogs)
    {
        parent::__construct($fileRoomAmountLogs);

        $this->fileRoomAmountLogs = array_values($fileRoomAmountLogs);
    }

    public function getFileRoomAmountLog(): FileRoomAmountLogs
    {
        return new FileRoomAmountLogs($this->fileRoomAmountLogs);
    }

    public function toArray(): array
    {
        return $this->fileRoomAmountLogs;
    }

    /**
     * @return array
     */
    public function jsonSerialize(): array
    {
        return $this->fileRoomAmountLogs;
    }
}
