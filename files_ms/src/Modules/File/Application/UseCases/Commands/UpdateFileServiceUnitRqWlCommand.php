<?php

namespace Src\Modules\File\Application\UseCases\Commands;

use Src\Modules\File\Domain\Model\FileItinerary;
use Src\Modules\File\Domain\Repositories\FileServiceUnitRepositoryInterface; 
use Src\Shared\Domain\CommandInterface;

class UpdateFileServiceUnitRqWlCommand implements CommandInterface
{
    private FileServiceUnitRepositoryInterface $fileServiceUnitRepository;

    public function __construct(private readonly int $fileServiceUnitId, private readonly array $params)
    {
        $this->fileServiceUnitRepository = app()->make(FileServiceUnitRepositoryInterface::class);
    }

    /**
     * @return mixed
     */
    public function execute(): bool
    {
        return $this->fileServiceUnitRepository->updateRqWl($this->fileServiceUnitId, $this->params);
    }
}
