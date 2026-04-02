<?php

namespace Src\Modules\File\Application\UseCases\Commands;

use Src\Modules\File\Domain\Model\FileItinerary;
use Src\Modules\File\Domain\Repositories\FileHotelRoomRepositoryInterface; 
use Src\Shared\Domain\CommandInterface;

class UpdateFileHotelRoomConfirmationCodeCommand implements CommandInterface
{
    private FileHotelRoomRepositoryInterface $fileHotelRoomRepository;

    public function __construct(private readonly int $hotelRoomId, private readonly string $code)
    {
        $this->fileHotelRoomRepository = app()->make(FileHotelRoomRepositoryInterface::class);
    }

    /**
     * @return mixed
     */
    public function execute(): array
    {
        return $this->fileHotelRoomRepository->updateConfirmationCode($this->hotelRoomId, $this->code);
    }
}
