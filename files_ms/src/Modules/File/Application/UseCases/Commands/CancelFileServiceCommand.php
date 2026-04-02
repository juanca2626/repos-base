<?php

namespace Src\Modules\File\Application\UseCases\Commands;

use Src\Modules\File\Domain\Model\FileItinerary;
use Src\Modules\File\Domain\Repositories\FileServiceRepositoryInterface; 
use Src\Shared\Domain\CommandInterface;

class CancelFileServiceCommand implements CommandInterface
{
    private FileServiceRepositoryInterface $fileHotelRoomRepository;

    public function __construct(private readonly int $id)
    {
        $this->fileHotelRoomRepository = app()->make(FileServiceRepositoryInterface::class);
    }

    /**
     * @return mixed
     */
    public function execute(): bool
    {
        return $this->fileHotelRoomRepository->cancel($this->id);
    }
}
