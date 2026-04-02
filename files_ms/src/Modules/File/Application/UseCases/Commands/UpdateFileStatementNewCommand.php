<?php

namespace Src\Modules\File\Application\UseCases\Commands;

use Src\Modules\File\Domain\Model\File;
use Src\Modules\File\Domain\Repositories\FileStatementRepositoryInterface;
use Src\Shared\Domain\CommandInterface;

class UpdateFileStatementNewCommand implements CommandInterface
{
    private FileStatementRepositoryInterface $repository;

    public function __construct(private readonly int $file ,private readonly array $params)
    {
        $this->repository = app()->make(FileStatementRepositoryInterface::class);
    }

    public function execute(): bool
    {
        return $this->repository->updateStatement($this->file , $this->params);
    }
}
