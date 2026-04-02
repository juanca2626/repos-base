<?php

namespace Src\Modules\File\Application\UseCases\Queries;

use Src\Modules\File\Domain\Repositories\FileHotelRoomRepositoryInterface;
use Src\Shared\Domain\QueryInterface;

class FindFileHotelRoomByIdQuery implements QueryInterface
{
    private FileHotelRoomRepositoryInterface $fileHotelRoomRepository;

    public function __construct(private readonly int $fileItineraryId)
    {
        $this->fileHotelRoomRepository = app()->make(FileHotelRoomRepositoryInterface::class);
    }

    /**
     * @return mixed
     */
    public function handle(): array
    {
        return $this->fileHotelRoomRepository->findById($this->fileItineraryId);
    }
}
