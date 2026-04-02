<?php

namespace Src\Modules\File\Application\UseCases\Commands;

use Src\Modules\File\Domain\Model\FileItinerary;
use Src\Modules\File\Domain\Repositories\FileServiceCompositionRepositoryInterface; 
use Src\Shared\Domain\CommandInterface;

class UpdateFileServiceCompositionConfirmationCodeCommand implements CommandInterface
{
    private FileServiceCompositionRepositoryInterface $fileHotelRoomRepository;

    public function __construct(private readonly int $hotelRoomId, private readonly string $code)
    {
        $this->fileHotelRoomRepository = app()->make(FileServiceCompositionRepositoryInterface::class);
    }

    /**
     * @return mixed
     */
    public function execute(): array
    {
        return $this->fileHotelRoomRepository->updateConfirmationCode($this->hotelRoomId, $this->code);
    }
}
