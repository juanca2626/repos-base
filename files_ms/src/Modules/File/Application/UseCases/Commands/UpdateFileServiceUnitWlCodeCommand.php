<?php

namespace Src\Modules\File\Application\UseCases\Commands;

use Src\Modules\File\Domain\Model\FileItinerary;
use Src\Modules\File\Domain\Repositories\FileServiceUnitRepositoryInterface; 
use Src\Shared\Domain\CommandInterface;

class UpdateFileServiceUnitWlCodeCommand implements CommandInterface
{
    private FileServiceUnitRepositoryInterface $fileServiceUnitId;

    public function __construct(private readonly int $hotelRoomId, private readonly string $code)
    {
        $this->fileServiceUnitId = app()->make(FileServiceUnitRepositoryInterface::class);
    }

    /**
     * @return mixed
     */
    public function execute(): bool
    {
        return $this->fileServiceUnitId->updateWlCode($this->hotelRoomId, $this->code);
    }
}
