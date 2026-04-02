<?php

namespace Src\Modules\File\Application\UseCases\Commands;

use Src\Modules\File\Domain\Model\FileVip;
use Src\Modules\File\Domain\Repositories\FilePassengerModifyPaxRepositoryInterface;
use Src\Shared\Domain\CommandInterface;

class UpdateFilePassengerModifyPaxCommand implements CommandInterface
{
    private FilePassengerModifyPaxRepositoryInterface $repository;

    public function __construct(private readonly int $fileId, private readonly array $params)
    {
        $this->repository = app()->make(FilePassengerModifyPaxRepositoryInterface::class);
    }

    public function execute(): bool
    {
        return $this->repository->update($this->fileId, $this->params);
    }
}
