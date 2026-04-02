<?php

namespace Src\Modules\File\Application\UseCases\Commands;
 
use Src\Modules\File\Domain\Repositories\FileHotelRoomUnitRepositoryInterface; 
use Src\Shared\Domain\CommandInterface;

class UpdateFileHotelRoomUnitAmountCostCommand implements CommandInterface
{
    private FileHotelRoomUnitRepositoryInterface $fileHotelRoomUnitRepository;

    public function __construct(private readonly int $id, private readonly float $newAmountCost)
    {
        $this->fileHotelRoomUnitRepository = app()->make(FileHotelRoomUnitRepositoryInterface::class);
    }

    /**
     * @return mixed
     */
    public function execute(): bool
    {
        return $this->fileHotelRoomUnitRepository->updateAmountCost($this->id, $this->newAmountCost);
    }
}
