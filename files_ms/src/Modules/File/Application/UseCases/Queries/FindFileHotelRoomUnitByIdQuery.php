<?php

namespace Src\Modules\File\Application\UseCases\Queries;
 
use Src\Modules\File\Domain\Repositories\FileHotelRoomUnitRepositoryInterface;
use Src\Shared\Domain\QueryInterface;

class FindFileHotelRoomUnitByIdQuery implements QueryInterface
{
    private FileHotelRoomUnitRepositoryInterface $fileHotelRoomUnitRepository;

    public function __construct(private readonly int $fileHotelRoomUnitId)
    {
        $this->fileHotelRoomUnitRepository = app()->make(FileHotelRoomUnitRepositoryInterface::class);
    }

    /**
     * @return mixed
     */
    public function handle(): array
    {
        return $this->fileHotelRoomUnitRepository->findById($this->fileHotelRoomUnitId);
    }
}
