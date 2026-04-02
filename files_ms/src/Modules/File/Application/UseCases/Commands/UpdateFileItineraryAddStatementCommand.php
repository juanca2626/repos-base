<?php

namespace Src\Modules\File\Application\UseCases\Commands;
 
use Src\Modules\File\Domain\Model\FileItinerary;
use Src\Modules\File\Domain\Repositories\FileItineraryRepositoryInterface;
use Src\Shared\Domain\CommandInterface;

class UpdateFileItineraryAddStatementCommand
{
    private FileItineraryRepositoryInterface $repository;

    public function __construct(private readonly int $file_id, private readonly array $params)
    {
        $this->repository = app()->make(FileItineraryRepositoryInterface::class);
    }

    public function execute(): bool
    {
        return $this->repository->update_add_statement($this->file_id,$this->params);
    }
}

