<?php

namespace Src\Modules\File\Domain\ValueObjects\FileService;


use Src\Shared\Domain\ValueObjects\ValueObjectArray;

final class FileServiceAmountLogs extends ValueObjectArray
{
    public readonly array $fileServiceAmountLogs;

    public function __construct(array $fileServiceAmountLogs)
    {
        parent::__construct($fileServiceAmountLogs);

        $this->fileServiceAmountLogs = array_values($fileServiceAmountLogs);
    }

    public function getFileRoomAmountLog(): FileServiceAmountLogs
    {
        return new FileServiceAmountLogs($this->fileServiceAmountLogs);
    }

    public function toArray(): array
    {
        return $this->fileServiceAmountLogs;
    }

    /**
     * @return array
     */
    public function jsonSerialize(): array
    {
        return $this->fileServiceAmountLogs;
    }
}
