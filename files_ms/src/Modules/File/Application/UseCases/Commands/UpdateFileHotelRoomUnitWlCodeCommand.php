<?php

namespace Src\Modules\File\Application\UseCases\Commands;

use Src\Modules\File\Domain\Model\FileItinerary;
use Src\Modules\File\Domain\Repositories\FileHotelRoomUnitRepositoryInterface; 
use Src\Shared\Domain\CommandInterface;

class UpdateFileHotelRoomUnitWlCodeCommand implements CommandInterface
{
    private FileHotelRoomUnitRepositoryInterface $fileHotelRoomRepository;

    public function __construct(private readonly int $hotelRoomId, private readonly string $code)
    {
        $this->fileHotelRoomRepository = app()->make(FileHotelRoomUnitRepositoryInterface::class);
    }

    /**
     * @return mixed
     */
    public function execute(): bool
    {
        return $this->fileHotelRoomRepository->updateWlCode($this->hotelRoomId, $this->code);
    }
}
