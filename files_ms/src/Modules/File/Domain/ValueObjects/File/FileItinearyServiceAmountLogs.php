<?php

namespace Src\Modules\File\Domain\ValueObjects\File;

use Src\Shared\Domain\ValueObjects\ValueObjectArray;

final class FileItinearyServiceAmountLogs extends ValueObjectArray
{
    public readonly array $fileItinearyServiceAmountLogs;

    public function __construct(array $fileItinearyServiceAmountLogs)
    {
        parent::__construct($fileItinearyServiceAmountLogs);
        $this->fileItinearyServiceAmountLogs = array_values($fileItinearyServiceAmountLogs);
    }

    public function getFileItinearyServiceAmountLogs(): FileItinearyServiceAmountLogs
    {
        return new FileItinearyServiceAmountLogs($this->fileItinearyServiceAmountLogs);
    }

    public function toArray(): array
    {
        return $this->fileItinearyServiceAmountLogs;
    }

    /**
     * @return array
     */
    public function jsonSerialize(): array
    {
        return $this->fileItinearyServiceAmountLogs;
    }
}
