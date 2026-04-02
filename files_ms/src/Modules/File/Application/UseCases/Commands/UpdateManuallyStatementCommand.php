<?php

namespace Src\Modules\File\Application\UseCases\Commands;
 
use Src\Modules\File\Domain\Model\FileItinerary;
use Src\Modules\File\Domain\Repositories\FileStatementRepositoryInterface;
use Src\Shared\Domain\CommandInterface;

class UpdateManuallyStatementCommand
{
    private FileStatementRepositoryInterface $repository;

    public function __construct(private readonly int $file_id, private readonly array $params)
    {
        $this->repository = app()->make(FileStatementRepositoryInterface::class);
    }

    public function execute(): bool
    {
        return $this->repository->updateManuallyStatement($this->file_id,$this->params);
    }
}

