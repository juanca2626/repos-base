<?php

namespace Src\Modules\File\Application\UseCases\Commands;

use Src\Modules\File\Domain\Model\FileVip;
use Src\Modules\File\Domain\Repositories\FilePassengerRepositoryInterface;
use Src\Shared\Domain\CommandInterface;

class UpdateFilePassengerCommand implements CommandInterface
{
    private FilePassengerRepositoryInterface $repository;

    public function __construct(private readonly int $fileId, private readonly array $params)
    {
        $this->repository = app()->make(FilePassengerRepositoryInterface::class);
    }

    public function execute(): bool
    {
        return $this->repository->updateAll($this->fileId, $this->params);
    }
}
