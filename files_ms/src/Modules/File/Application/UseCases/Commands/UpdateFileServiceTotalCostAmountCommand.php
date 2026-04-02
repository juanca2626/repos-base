<?php

namespace Src\Modules\File\Application\UseCases\Commands;

use Src\Modules\File\Domain\Repositories\FileServiceRepositoryInterface;
use Src\Shared\Domain\CommandInterface;

class UpdateFileServiceTotalCostAmountCommand implements CommandInterface
{
    private FileServiceRepositoryInterface $fileServiceRepository;

    public function __construct(private readonly int $fileServiceId)
    {
        $this->fileServiceRepository = app()->make(FileServiceRepositoryInterface::class);
    }

    public function execute(): bool
    {
        return $this->fileServiceRepository->updateTotalAmountCost($this->fileServiceId);
    }
}
