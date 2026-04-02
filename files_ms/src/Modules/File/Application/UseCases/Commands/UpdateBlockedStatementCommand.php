<?php

namespace Src\Modules\File\Application\UseCases\Commands;

use Src\Modules\File\Domain\Repositories\FileStatementRepositoryInterface;
use Src\Shared\Domain\CommandInterface;

class UpdateBlockedStatementCommand implements CommandInterface
{
    private FileStatementRepositoryInterface $fileStatementRepository;

    public function __construct(private readonly array $params)
    {
        $this->fileStatementRepository = app()->make(FileStatementRepositoryInterface::class);
    }

    public function execute(): bool
    {
        return $this->fileStatementRepository->desBlocked($this->params);
    }
}
