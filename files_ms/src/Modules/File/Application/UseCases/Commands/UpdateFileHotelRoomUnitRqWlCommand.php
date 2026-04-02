<?php

namespace Src\Modules\File\Application\UseCases\Commands;

use Src\Modules\File\Domain\Model\FileItinerary;
use Src\Modules\File\Domain\Repositories\FileHotelRoomUnitRepositoryInterface; 
use Src\Shared\Domain\CommandInterface;

class UpdateFileHotelRoomUnitRqWlCommand implements CommandInterface
{
    private FileHotelRoomUnitRepositoryInterface $fileHotelRoomRepository;

    public function __construct(private readonly int $hotelRoomId, private readonly array $params)
    {
        $this->fileHotelRoomRepository = app()->make(FileHotelRoomUnitRepositoryInterface::class);
    }

    /**
     * @return mixed
     */
    public function execute(): bool
    {
        return $this->fileHotelRoomRepository->updateRqWl($this->hotelRoomId, $this->params);
    }
}
