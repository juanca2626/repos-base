<?php

namespace Src\Modules\File\Application\UseCases\Commands;
 
use Src\Modules\File\Domain\Repositories\FileServiceCompositionRepositoryInterface; 
use Src\Shared\Domain\CommandInterface;

class UpdateFileServiceCompositionAmountCostCommand implements CommandInterface
{
    private FileServiceCompositionRepositoryInterface $fileServiceCompositionRepository;

    public function __construct(private readonly int $id, private readonly float $newAmountCost)
    {
        $this->fileServiceCompositionRepository = app()->make(FileServiceCompositionRepositoryInterface::class);
    }

    /**
     * @return mixed
     */
    public function execute(): bool
    {
        return $this->fileServiceCompositionRepository->updateAmountCost($this->id, $this->newAmountCost);
    }
}
