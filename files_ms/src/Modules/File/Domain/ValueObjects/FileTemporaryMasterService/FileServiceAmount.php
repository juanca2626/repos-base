<?php

namespace Src\Modules\File\Domain\ValueObjects\FileTemporaryMasterService;


use Src\Shared\Domain\ValueObject;

final class FileServiceAmount extends ValueObject
{
    public readonly object $fileServiceAmount;

    public function __construct(object $fileServiceAmount)
    { 
        $this->fileServiceAmount = $fileServiceAmount;
    }

    public function getFileRoomAmountLog(): FileServiceAmount
    {
        return new FileServiceAmount($this->fileServiceAmount);
    }

    public function toArray(): object
    {
        return $this->fileServiceAmount;
    }

    /**
     * @return object
     */
    public function jsonSerialize(): object
    {
        return $this->fileServiceAmount;
    }
}
