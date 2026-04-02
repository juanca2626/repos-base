<?php

namespace Src\Modules\File\Application\UseCases\Commands;

use Src\Modules\File\Domain\Model\FileItinerary;
use Src\Modules\File\Domain\Repositories\FileHotelRoomRepositoryInterface; 
use Src\Shared\Domain\CommandInterface;

class CancelFileHotelRoomCommand implements CommandInterface
{
    private FileHotelRoomRepositoryInterface $fileHotelRoomRepository;

    public function __construct(private readonly int $id)
    {
        $this->fileHotelRoomRepository = app()->make(FileHotelRoomRepositoryInterface::class);
    }

    /**
     * @return mixed
     */
    public function execute(): bool
    {
        return $this->fileHotelRoomRepository->cancel($this->id);
    }
}
