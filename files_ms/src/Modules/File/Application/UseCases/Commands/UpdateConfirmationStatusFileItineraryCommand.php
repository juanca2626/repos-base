<?php

namespace Src\Modules\File\Application\UseCases\Commands;

use Src\Modules\File\Domain\Model\FileVip;
use Src\Modules\File\Domain\Repositories\FileItineraryRepositoryInterface; 
use Src\Shared\Domain\CommandInterface;

class UpdateConfirmationStatusFileItineraryCommand implements CommandInterface
{
    private FileItineraryRepositoryInterface $repository;

    public function __construct(private readonly int $fileId, private readonly array $params)
    {
        $this->repository = app()->make(FileItineraryRepositoryInterface::class);
    }

    public function execute(): bool
    {
        return $this->repository->updateConfirmationStatus($this->fileId, $this->params);
    }
}
