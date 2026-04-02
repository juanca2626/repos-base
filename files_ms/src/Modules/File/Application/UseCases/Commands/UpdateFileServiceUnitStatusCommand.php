<?php

namespace Src\Modules\File\Application\UseCases\Commands;
 
use Src\Modules\File\Domain\Repositories\FileServiceUnitRepositoryInterface; 
use Src\Shared\Domain\CommandInterface;

class UpdateFileServiceUnitStatusCommand implements CommandInterface
{
    private FileServiceUnitRepositoryInterface $fileServiceUnitRepository;
        

    public function __construct(private readonly int $id, private readonly int $status)
    {
        $this->fileServiceUnitRepository = app()->make(FileServiceUnitRepositoryInterface::class);
    }

    /**
     * @return mixed
     */
    public function execute(): bool
    {
        return $this->fileServiceUnitRepository->updateStatus($this->id, $this->status);
    }
}
